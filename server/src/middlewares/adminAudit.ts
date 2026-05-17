import { NextFunction, Request, Response } from 'express';
import { prisma } from '../config/prisma.js';
import { isAdminRole } from '../permissions/resolver.js';

export function flagAdminAudit(actionType: string, description: string, options: { targetUserId?: string; affectedField?: string; metadata?: Record<string, unknown> } = {}) {
  return (request: Request, _response: Response, next: NextFunction) => {
    request.auditContext = {
      actionType,
      description,
      targetUserId: options.targetUserId,
      affectedField: options.affectedField,
      metadata: options.metadata
    };
    next();
  };
}

export async function persistAuditIfNeeded(request: Request) {
  if (!request.user || !request.auditContext || !isAdminRole(request.user.role)) {
    return;
  }

  await prisma.adminActivityLog.create({
    data: {
      adminId: request.user.id,
      targetUserId: request.auditContext.targetUserId,
      actionType: request.auditContext.actionType as never,
      description: request.auditContext.description,
      affectedField: request.auditContext.affectedField,
      metadata: (request.auditContext.metadata ?? {}) as never
    }
  });
}
