import { Router } from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { acceptFriend, listFriends, requestFriend } from '../controllers/friend.controller.js';

export const friendRouter = Router();
friendRouter.use(requireAuth);
friendRouter.get('/', listFriends);
friendRouter.post('/request/:recipientId', requestFriend);
friendRouter.post('/accept/:senderId', acceptFriend);
