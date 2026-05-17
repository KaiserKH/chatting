import { NextFunction, Request, Response } from 'express';
import type { RoleName } from '../config/constants.js';
import { isAdminRole } from '../permissions/resolver.js';

export function requireRole(...roles: RoleName[]) {
  return (request: Request, response: Response, next: NextFunction) => {
    if (!request.user) {
      return response.status(401).json({ success: false, message: 'Unauthorized' });
    }

    if (!roles.includes(request.user.role) && !isAdminRole(request.user.role)) {
      return response.status(403).json({ success: false, message: 'Forbidden' });
    }

    return next();
  };
}
