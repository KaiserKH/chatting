import { Request, Response } from 'express';
import { ok } from '../utils/http.js';
import { sendFriendRequest, acceptFriendRequest } from '../services/friend.service.js';
import { prisma } from '../config/prisma.js';

export async function requestFriend(request: Request, response: Response) {
  const result = await sendFriendRequest(request.user!.id, String(request.params.recipientId));
  return ok(response, result, 'Friend request sent', 201);
}

export async function acceptFriend(request: Request, response: Response) {
  const result = await acceptFriendRequest(String(request.params.senderId), request.user!.id);
  return ok(response, result, 'Friend request accepted');
}

export async function listFriends(request: Request, response: Response) {
  const friendships = await prisma.friendship.findMany({
    where: {
      OR: [{ userAId: request.user?.id }, { userBId: request.user?.id }]
    },
    include: {
      userA: { select: { id: true, username: true, avatarUrl: true } },
      userB: { select: { id: true, username: true, avatarUrl: true } }
    }
  });
  return ok(response, friendships, 'Friends');
}
