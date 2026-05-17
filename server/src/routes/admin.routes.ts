import { Router } from 'express';
import { requireAuth } from '../middlewares/auth.js';
import { requireRole } from '../middlewares/authorize.js';
import { dashboard, logs, permissions, updateUserRole, users } from '../controllers/admin.controller.js';
import { roleHierarchy } from '../config/constants.js';

export const adminRouter = Router();
adminRouter.use(requireAuth, requireRole(...roleHierarchy.slice(0, 3)));
adminRouter.get('/dashboard', dashboard);
adminRouter.get('/users', users);
adminRouter.patch('/users/:id/role', updateUserRole);
adminRouter.get('/permissions', permissions);
adminRouter.get('/logs', logs);
