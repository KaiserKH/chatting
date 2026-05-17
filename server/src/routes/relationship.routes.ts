import { Router } from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { assignTag, listTags } from '../controllers/relationship.controller.js';

export const relationshipRouter = Router();
relationshipRouter.use(requireAuth);
relationshipRouter.get('/tags', listTags);
relationshipRouter.post('/tags', assignTag);
