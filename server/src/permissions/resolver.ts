import { Permission, User } from '@prisma/client';
import { prisma } from '../config/prisma.js';
import type { RoleName } from '../config/constants.js';

const adminRoles: RoleName[] = ['SUPER_ADMIN', 'ADMIN', 'MODERATOR'];

const cache = new Map<string, { value: boolean; expiresAt: number }>();
const ttlMs = 30_000;

export function isAdminRole(role: RoleName): boolean {
  return adminRoles.includes(role);
}

export async function resolvePermission(user: User & { role: { name: RoleName } }, permissionKey: string): Promise<boolean> {
  const cacheKey = `${user.id}:${permissionKey}`;
  const cached = cache.get(cacheKey);
  if (cached && cached.expiresAt > Date.now()) {
    return cached.value;
  }

  const permission = await prisma.permission.findUnique({ where: { key: permissionKey } });
  if (!permission) {
    return false;
  }

  const userSetting = await prisma.userPermission.findUnique({
    where: {
      userId_permissionId: {
        userId: user.id,
        permissionId: permission.id
      }
    }
  });

  if (userSetting) {
    cache.set(cacheKey, { value: userSetting.enabled, expiresAt: Date.now() + ttlMs });
    return userSetting.enabled;
  }

  const roleSetting = await prisma.rolePermission.findFirst({
    where: {
      role: { name: user.role.name },
      permissionId: permission.id
    }
  });

  if (roleSetting) {
    cache.set(cacheKey, { value: roleSetting.enabled, expiresAt: Date.now() + ttlMs });
    return roleSetting.enabled;
  }

  cache.set(cacheKey, { value: permission.defaultValue, expiresAt: Date.now() + ttlMs });
  return permission.defaultValue;
}

export async function assertPermission(user: User & { role: { name: RoleName } }, permissionKey: string): Promise<void> {
  if (isAdminRole(user.role.name)) {
    return;
  }

  const allowed = await resolvePermission(user, permissionKey);
  if (!allowed) {
    throw new Error(`Permission denied: ${permissionKey}`);
  }
}
