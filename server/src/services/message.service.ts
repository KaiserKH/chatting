import { prisma } from '../config/prisma.js';

export type MessageType = 'TEXT' | 'IMAGE' | 'VIDEO' | 'FILE' | 'VOICE_NOTE';

export async function getOrCreateDirectConversation(userId: string, peerId: string) {
  const existing = await prisma.conversation.findFirst({
    where: {
      isDirect: true,
      participants: {
        every: {
          userId: { in: [userId, peerId] }
        }
      }
    },
    include: { participants: true }
  });

  if (existing) {
    return existing;
  }

  return prisma.conversation.create({
    data: {
      isDirect: true,
      participants: {
        create: [{ userId }, { userId: peerId }]
      }
    },
    include: { participants: true }
  });
}

export async function createMessage(input: {
  conversationId: string;
  senderId: string;
  text?: string;
  type?: MessageType;
  mediaUrl?: string;
  mediaMimeType?: string;
  mediaSizeBytes?: number;
  replyToMessageId?: string;
}) {
  const message = await prisma.message.create({
    data: {
      conversationId: input.conversationId,
      senderId: input.senderId,
      text: input.text,
      type: input.type ?? 'TEXT',
      mediaUrl: input.mediaUrl,
      mediaMimeType: input.mediaMimeType,
      mediaSizeBytes: input.mediaSizeBytes,
      replyToMessageId: input.replyToMessageId
    },
    include: {
      sender: { select: { id: true, username: true, avatarUrl: true } }
    }
  });

  return message;
}

export async function listMessages(conversationId: string, cursor?: string, take = 30) {
  return prisma.message.findMany({
    where: { conversationId },
    take,
    ...(cursor ? { cursor: { id: cursor }, skip: 1 } : {}),
    orderBy: { createdAt: 'desc' },
    include: {
      sender: { select: { id: true, username: true, avatarUrl: true } },
      reactions: true,
      edits: true
    }
  });
}
