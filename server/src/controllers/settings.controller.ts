import { Request, Response } from 'express';
import { ok } from '../utils/http.js';
import { prisma } from '../config/prisma.js';

export async function getSettings(request: Request, response: Response) {
  const settings = await prisma.userSetting.findUnique({ where: { userId: request.user!.id } });
  return ok(response, settings, 'Settings');
}

export async function updateSettings(request: Request, response: Response) {
  const settings = await prisma.userSetting.upsert({
    where: { userId: request.user!.id },
    update: { payload: request.body },
    create: { userId: request.user!.id, payload: request.body }
  });
  return ok(response, settings, 'Settings updated');
}
