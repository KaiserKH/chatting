import { Router } from 'express'
import { healthRouter } from './health.routes.js'
import { authRouter } from './auth.routes.js'
import { userRouter } from './user.routes.js'
import { friendRouter } from './friend.routes.js'
import { messageRouter } from './message.routes.js'
import { adminRouter } from './admin.routes.js'

export const apiRouter = Router()

apiRouter.use('/health', healthRouter)
apiRouter.use('/auth', authRouter)
apiRouter.use('/users', userRouter)
apiRouter.use('/friends', friendRouter)
apiRouter.use('/messages', messageRouter)
apiRouter.use('/admin', adminRouter)
