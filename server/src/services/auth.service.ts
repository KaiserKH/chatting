import { prisma } from '../config/prisma.js';
import { comparePassword, hashPassword } from '../utils/password.js';
import { signAccessToken, signRefreshToken } from '../utils/tokens.js';
import type { RoleName } from '../config/constants.js';

function tokenExpiryDate(days = 7) {
  return new Date(Date.now() + days * 24 * 60 * 60 * 1000);
}

export async function registerUser(input: {
  username: string;
  phone: string;
  email?: string | null;
  password: string;
  deviceName: string;
  ipAddress: string;
  userAgent: string;
}) {
  const existingUsername = await prisma.user.findUnique({ where: { username: input.username } });
  if (existingUsername) {
    throw new Error('Username already exists');
  }

  const defaultRole = await prisma.role.findUnique({ where: { name: 'NORMAL_USER' as RoleName } });
  if (!defaultRole) {
    throw new Error('Default role missing');
  }

  const passwordHash = await hashPassword(input.password);
  const user = await prisma.user.create({
    data: {
      username: input.username,
      phone: input.phone,
      email: input.email,
      passwordHash,
      roleId: defaultRole.id,
      displayName: input.username,
      settings: {
        create: {
          payload: {
            theme: 'dark',
            fontSize: 'medium'
          }
        }
      }
    },
    include: { role: true }
  });

  const session = await prisma.userSession.create({
    data: {
      userId: user.id,
      refreshTokenHash: '',
      deviceName: input.deviceName,
      ipAddress: input.ipAddress,
      userAgent: input.userAgent,
      expiresAt: tokenExpiryDate()
    }
  });

  const accessToken = signAccessToken({ sub: user.id, sessionId: session.id, role: user.role.name, username: user.username });
  const refreshToken = signRefreshToken({ sub: user.id, sessionId: session.id, role: user.role.name, username: user.username });
  const refreshTokenHash = await hashPassword(refreshToken);

  await prisma.userSession.update({ where: { id: session.id }, data: { refreshTokenHash } });
  await prisma.userDevice.create({
    data: {
      userId: user.id,
      deviceName: input.deviceName,
      ipAddress: input.ipAddress,
      userAgent: input.userAgent
    }
  });

  await prisma.loginHistory.create({
    data: {
      userId: user.id,
      deviceName: input.deviceName,
      ipAddress: input.ipAddress,
      userAgent: input.userAgent
    }
  });

  return { user, accessToken, refreshToken, session };
}

export async function loginUser(input: {
  identifier: string;
  password: string;
  deviceName: string;
  ipAddress: string;
  userAgent: string;
}) {
  const user = await prisma.user.findFirst({
    where: {
      OR: [{ username: input.identifier }, { phone: input.identifier }]
    },
    include: { role: true }
  });

  if (!user) {
    throw new Error('Invalid credentials');
  }

  if (!user.isActive || user.isBanned || user.isSuspended) {
    throw new Error('Account is not available');
  }

  const ok = await comparePassword(input.password, user.passwordHash);
  if (!ok) {
    throw new Error('Invalid credentials');
  }

  const session = await prisma.userSession.create({
    data: {
      userId: user.id,
      refreshTokenHash: '',
      deviceName: input.deviceName,
      ipAddress: input.ipAddress,
      userAgent: input.userAgent,
      expiresAt: tokenExpiryDate()
    }
  });

  const accessToken = signAccessToken({ sub: user.id, sessionId: session.id, role: user.role.name, username: user.username });
  const refreshToken = signRefreshToken({ sub: user.id, sessionId: session.id, role: user.role.name, username: user.username });
  const refreshTokenHash = await hashPassword(refreshToken);

  await prisma.userSession.update({ where: { id: session.id }, data: { refreshTokenHash } });
  await prisma.loginHistory.create({
    data: {
      userId: user.id,
      deviceName: input.deviceName,
      ipAddress: input.ipAddress,
      userAgent: input.userAgent
    }
  });

  return { user, accessToken, refreshToken, session };
}

export async function rotateRefreshSession(sessionId: string, refreshToken: string) {
  const session = await prisma.userSession.findUnique({
    where: { id: sessionId },
    include: { user: { include: { role: true } } }
  });

  if (!session || session.revokedAt || session.expiresAt < new Date()) {
    throw new Error('Session expired');
  }

  const tokenOk = await comparePassword(refreshToken, session.refreshTokenHash);
  if (!tokenOk) {
    throw new Error('Invalid refresh token');
  }

  const accessToken = signAccessToken({
    sub: session.user.id,
    sessionId: session.id,
    role: session.user.role.name,
    username: session.user.username
  });
  const nextRefreshToken = signRefreshToken({
    sub: session.user.id,
    sessionId: session.id,
    role: session.user.role.name,
    username: session.user.username
  });
  const nextHash = await hashPassword(nextRefreshToken);

  await prisma.userSession.update({
    where: { id: session.id },
    data: { refreshTokenHash: nextHash, lastUsedAt: new Date(), expiresAt: tokenExpiryDate() }
  });

  return { user: session.user, accessToken, refreshToken: nextRefreshToken };
}

export async function revokeSession(sessionId: string) {
  await prisma.userSession.update({ where: { id: sessionId }, data: { revokedAt: new Date() } });
}

export async function revokeAllSessions(userId: string, excludeSessionId?: string) {
  await prisma.userSession.updateMany({
    where: {
      userId,
      ...(excludeSessionId ? { id: { not: excludeSessionId } } : {})
    },
    data: { revokedAt: new Date() }
  });
}
