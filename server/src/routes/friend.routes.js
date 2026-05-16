import { Router } from 'express'
import { friendController } from '../controllers/friend.controller.js'

export const friendRouter = Router()

friendRouter.get('/', friendController.list)
