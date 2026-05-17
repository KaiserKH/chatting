import { Request, Response } from 'express';
import { ok } from '../utils/http.js';
import { prisma } from '../config/prisma.js';

export async function listTags(_request: Request, response: Response) {
  return ok(response, await prisma.relationshipTag.findMany({ orderBy: { displayName: 'asc' } }), 'Relationship tags');
}

export async function assignTag(request: Request, response: Response) {
  const { targetUserId, tagId, notes } = request.body as { targetUserId: string; tagId: string; notes?: string };
  const relationship = await prisma.userRelationship.upsert({
    where: {
      ownerId_targetUserId_tagId: {
        ownerId: request.user!.id,
        targetUserId,
        tagId
      }
    },
    update: { notes },
    create: { ownerId: request.user!.id, targetUserId, tagId, notes }
  });
  return ok(response, relationship, 'Relationship saved', 201);
}
