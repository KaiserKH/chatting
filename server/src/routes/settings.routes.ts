import { Router } from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { getSettings, updateSettings } from '../controllers/settings.controller.js';

export const settingsRouter = Router();
settingsRouter.use(requireAuth);
settingsRouter.get('/', getSettings);
settingsRouter.put('/', updateSettings);
