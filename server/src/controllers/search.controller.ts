import { Request, Response } from 'express';
import { ok } from '../utils/http.js';
import { prisma } from '../config/prisma.js';

export async function searchUsers(request: Request, response: Response) {
  const query = String(request.query.query ?? '').trim();
  const users = await prisma.user.findMany({
    where: {
      OR: [{ username: { equals: query } }, { phone: { equals: query } }, { email: { contains: query } }]
    },
    include: { role: true },
    take: 20
  });
  return ok(response, users, 'Search results');
}
