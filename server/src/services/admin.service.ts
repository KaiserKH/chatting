import { AuditActionType } from '@prisma/client';
import { prisma } from '../config/prisma.js';

export async function getAdminDashboardSummary() {
  const [users, messages, sessions, logs] = await Promise.all([
    prisma.user.count(),
    prisma.message.count(),
    prisma.userSession.count({ where: { revokedAt: null } }),
    prisma.adminActivityLog.count()
  ]);

  return { users, messages, sessions, logs };
}

export async function createAdminLog(input: {
  adminId: string;
  targetUserId?: string;
  actionType: AuditActionType;
  description: string;
  affectedField?: string;
  metadata?: Record<string, unknown>;
}) {
  return prisma.adminActivityLog.create({
    data: {
      adminId: input.adminId,
      targetUserId: input.targetUserId,
      actionType: input.actionType,
      description: input.description,
      affectedField: input.affectedField,
      metadata: (input.metadata ?? {}) as never
    }
  });
}
