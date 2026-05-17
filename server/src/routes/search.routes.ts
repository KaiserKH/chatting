import { Router } from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { searchUsers } from '../controllers/search.controller.js';

export const searchRouter = Router();
searchRouter.use(requireAuth);
searchRouter.get('/users', searchUsers);
