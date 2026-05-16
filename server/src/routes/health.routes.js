import { Router } from 'express'

export const healthRouter = Router()

healthRouter.get('/', (_req, res) => {
  res.json({ success: true, status: 'ok', service: 'chatting-server' })
})
