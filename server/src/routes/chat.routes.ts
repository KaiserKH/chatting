import { Router } from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { fetchMessages, listConversations, openConversation, postMessage, searchMessages } from '../controllers/chat.controller.js';

export const chatRouter = Router();

chatRouter.use(requireAuth);
chatRouter.get('/conversations', listConversations);
chatRouter.get('/conversations/:peerId', openConversation);
chatRouter.get('/conversations/:conversationId/messages', fetchMessages);
chatRouter.post('/messages', postMessage);
chatRouter.get('/messages/search', searchMessages);
