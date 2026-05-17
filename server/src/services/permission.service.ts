import { prisma } from '../config/prisma.js';
import { isAdminRole } from '../permissions/resolver.js';

export async function listEffectivePermissions(userId: string) {
  const user = await prisma.user.findUnique({
    where: { id: userId },
    include: {
      role: { include: { permissions: { include: { permission: true } } } },
      userPermissions: { include: { permission: true } }
    }
  });

  if (!user) {
    throw new Error('User not found');
  }

  const permissions = await prisma.permission.findMany();
  const resolved = permissions.map((permission) => {
    const userOverride = user.userPermissions.find((entry) => entry.permissionId === permission.id);
    if (userOverride) {
      return { key: permission.key, enabled: userOverride.enabled };
    }

    const roleOverride = user.role.permissions.find((entry) => entry.permissionId === permission.id);
    if (roleOverride) {
      return { key: permission.key, enabled: roleOverride.enabled };
    }

    return { key: permission.key, enabled: permission.defaultValue || isAdminRole(user.role.name) };
  });

  return resolved;
}
