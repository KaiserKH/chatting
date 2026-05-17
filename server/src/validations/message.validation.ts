import { z } from 'zod';

export const sendMessageSchema = z.object({
  conversationId: z.string().optional(),
  recipientId: z.string().optional(),
  text: z.string().max(5000).optional(),
  replyToMessageId: z.string().optional(),
  type: z.enum(['TEXT', 'IMAGE', 'VIDEO', 'FILE', 'VOICE_NOTE']).default('TEXT'),
  mediaUrl: z.string().url().optional(),
  mediaMimeType: z.string().optional(),
  mediaSizeBytes: z.number().int().nonnegative().optional()
}).refine((payload) => Boolean(payload.conversationId || payload.recipientId), {
  message: 'conversationId or recipientId is required'
});
