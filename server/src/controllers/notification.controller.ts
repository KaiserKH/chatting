import { Request, Response } from 'express';
import { ok } from '../utils/http.js';
import { prisma } from '../config/prisma.js';

export async function listNotifications(request: Request, response: Response) {
  return ok(response, await prisma.notification.findMany({ where: { userId: request.user!.id }, orderBy: { createdAt: 'desc' } }), 'Notifications');
}

export async function markNotificationRead(request: Request, response: Response) {
  const notification = await prisma.notification.update({ where: { id: String(request.params.id) }, data: { readAt: new Date() } });
  return ok(response, notification, 'Notification marked read');
}
