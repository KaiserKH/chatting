import { Request, Response } from 'express';
import { ok } from '../utils/http.js';
import { prisma } from '../config/prisma.js';
import { createMessage, getOrCreateDirectConversation, listMessages } from '../services/message.service.js';
import { sendMessageSchema } from '../validations/message.validation.js';

type MessageType = 'TEXT' | 'IMAGE' | 'VIDEO' | 'FILE' | 'VOICE_NOTE';

export async function listConversations(request: Request, response: Response) {
  const conversations = await prisma.conversation.findMany({
    where: { participants: { some: { userId: request.user?.id } } },
    include: {
      participants: { include: { user: { select: { id: true, username: true, avatarUrl: true, lastSeenAt: true } } } },
      messages: { take: 1, orderBy: { createdAt: 'desc' } }
    },
    orderBy: { updatedAt: 'desc' }
  });
  return ok(response, conversations, 'Conversations');
}

export async function openConversation(request: Request, response: Response) {
  const peerId = String(request.params.peerId);
  const conversation = await getOrCreateDirectConversation(request.user!.id, peerId);
  return ok(response, conversation, 'Conversation opened');
}

export async function fetchMessages(request: Request, response: Response) {
  const conversationId = String(request.params.conversationId);
  const cursor = typeof request.query.cursor === 'string' ? request.query.cursor : undefined;
  const take = typeof request.query.take === 'string' ? Number(request.query.take) : 30;
  const messages = await listMessages(conversationId, cursor, take);
  return ok(response, messages, 'Messages');
}

export async function postMessage(request: Request, response: Response) {
  const payload = sendMessageSchema.parse(request.body);
  const conversationId = payload.conversationId ?? (await getOrCreateDirectConversation(request.user!.id, payload.recipientId!)).id;
  const message = await createMessage({
    conversationId,
    senderId: request.user!.id,
    text: payload.text,
    type: payload.type as MessageType,
    mediaUrl: payload.mediaUrl,
    mediaMimeType: payload.mediaMimeType,
    mediaSizeBytes: payload.mediaSizeBytes,
    replyToMessageId: payload.replyToMessageId
  });
  return ok(response, message, 'Message sent', 201);
}

export async function searchMessages(request: Request, response: Response) {
  const query = String(request.query.query ?? '').trim();
  const results = await prisma.message.findMany({
    where: {
      conversation: { participants: { some: { userId: request.user?.id } } },
      text: { contains: query }
    },
    take: 50,
    orderBy: { createdAt: 'desc' }
  });
  return ok(response, results, 'Search results');
}
