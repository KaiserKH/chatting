import { Router } from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { listNotifications, markNotificationRead } from '../controllers/notification.controller.js';

export const notificationRouter = Router();
notificationRouter.use(requireAuth);
notificationRouter.get('/', listNotifications);
notificationRouter.patch('/:id/read', markNotificationRead);
