import { Router } from 'express'
import { messageController } from '../controllers/message.controller.js'

export const messageRouter = Router()

messageRouter.get('/', messageController.list)
