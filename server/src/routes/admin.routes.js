import { Router } from 'express'
import { adminController } from '../controllers/admin.controller.js'

export const adminRouter = Router()

adminRouter.get('/dashboard', adminController.dashboard)
