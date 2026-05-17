import { prisma } from '../config/prisma.js';

export async function sendFriendRequest(senderId: string, recipientId: string) {
  return prisma.friendRequest.upsert({
    where: {
      senderId_recipientId: { senderId, recipientId }
    },
    update: { status: 'PENDING' },
    create: { senderId, recipientId }
  });
}

export async function acceptFriendRequest(senderId: string, recipientId: string) {
  const request = await prisma.friendRequest.update({
    where: { senderId_recipientId: { senderId, recipientId } },
    data: { status: 'ACCEPTED' }
  });

  const pair = [senderId, recipientId].sort();
  await prisma.friendship.upsert({
    where: { userAId_userBId: { userAId: pair[0], userBId: pair[1] } },
    update: {},
    create: { userAId: pair[0], userBId: pair[1] }
  });

  return request;
}
