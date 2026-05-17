import { Request, Response } from 'express';
import { ok } from '../utils/http.js';
import { getAdminDashboardSummary } from '../services/admin.service.js';
import { prisma } from '../config/prisma.js';
import { persistAuditIfNeeded } from '../middlewares/adminAudit.js';
import type { RoleName } from '../config/constants.js';

export async function dashboard(request: Request, response: Response) {
  return ok(response, await getAdminDashboardSummary(), 'Dashboard');
}

export async function users(request: Request, response: Response) {
  const users = await prisma.user.findMany({ include: { role: true }, orderBy: { createdAt: 'desc' } });
  return ok(response, users, 'Users');
}

export async function updateUserRole(request: Request, response: Response) {
  const role = request.body.role as RoleName;
  const updated = await prisma.user.update({ where: { id: String(request.params.id) }, data: { role: { connect: { name: role } } } });
  request.auditContext = {
    actionType: 'ACCOUNT_MODIFIED',
    description: `Role updated to ${role}`,
    targetUserId: updated.id,
    affectedField: 'role',
    metadata: { role }
  };
  await persistAuditIfNeeded(request);
  return ok(response, updated, 'Role updated');
}

export async function logs(request: Request, response: Response) {
  const logs = await prisma.adminActivityLog.findMany({
    include: { admin: { select: { id: true, username: true } }, targetUser: { select: { id: true, username: true } } },
    orderBy: { createdAt: 'desc' }
  });
  return ok(response, logs, 'Audit logs');
}

export async function permissions(request: Request, response: Response) {
  const permissions = await prisma.permission.findMany({
    include: { roleSettings: true, userSettings: true },
    orderBy: { key: 'asc' }
  });
  return ok(response, permissions, 'Permissions');
}
