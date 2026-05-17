import { Server, Socket } from 'socket.io';
import { verifyAccessToken } from '../utils/tokens.js';
import { prisma } from '../config/prisma.js';
import { createMessage } from '../services/message.service.js';
import { env } from '../config/env.js';

type AuthedSocket = Socket & {
  user?: { id: string; username: string; sessionId: string };
};

export function registerSocketHandlers(io: Server) {
  io.use(async (socket: AuthedSocket, next) => {
    const raw = socket.handshake.auth?.token || socket.handshake.headers.authorization?.toString().replace('Bearer ', '');
    if (!raw) {
      return next(new Error('Unauthorized'));
    }

    try {
      const payload = verifyAccessToken(raw);
      socket.user = { id: payload.sub, username: payload.username, sessionId: payload.sessionId };
      return next();
    } catch {
      return next(new Error('Unauthorized'));
    }
  });

  io.on('connection', async (socket: AuthedSocket) => {
    if (!socket.user) return;
    const currentUser = socket.user;

    await prisma.user.update({ where: { id: currentUser.id }, data: { lastSeenAt: new Date() } });
    socket.join(`user:${currentUser.id}`);
    socket.emit('user:online', { userId: currentUser.id });

    socket.on('typing:start', ({ conversationId }) => socket.to(`conversation:${conversationId}`).emit('typing:start', { userId: currentUser.id, conversationId }));
    socket.on('typing:stop', ({ conversationId }) => socket.to(`conversation:${conversationId}`).emit('typing:stop', { userId: currentUser.id, conversationId }));

    socket.on('message:send', async (payload, callback) => {
      try {
        const message = await createMessage({
          conversationId: payload.conversationId,
          senderId: currentUser.id,
          text: payload.text,
          mediaUrl: payload.mediaUrl,
          mediaMimeType: payload.mediaMimeType,
          mediaSizeBytes: payload.mediaSizeBytes,
          replyToMessageId: payload.replyToMessageId
        });
        io.to(`conversation:${payload.conversationId}`).emit('message:new', message);
        callback?.({ ok: true, message });
      } catch (error) {
        callback?.({ ok: false, error: (error as Error).message });
      }
    });

    socket.on('call:signal', (payload) => {
      io.to(`user:${payload.toUserId}`).emit('call:signal', { ...payload, fromUserId: socket.user!.id, turn: {
        url: env.TURN_SERVER_URL,
        username: env.TURN_SERVER_USERNAME,
        credential: env.TURN_SERVER_CREDENTIAL
      }});
    });

    socket.on('notification:new', async (payload) => {
      await prisma.notification.create({
        data: {
          userId: payload.userId,
          type: payload.type,
          title: payload.title,
          body: payload.body,
          data: payload.data ?? {}
        }
      });
      io.to(`user:${payload.userId}`).emit('notification:new', payload);
    });

    socket.on('disconnect', async () => {
      await prisma.user.update({ where: { id: socket.user!.id }, data: { lastSeenAt: new Date() } });
      io.to(`user:${currentUser.id}`).emit('user:offline', { userId: currentUser.id });
    });
  });
}
