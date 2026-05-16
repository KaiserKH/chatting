import express from 'express'
import cors from 'cors'
import helmet from 'helmet'
import cookieParser from 'cookie-parser'
import morgan from 'morgan'
import csurf from 'csurf'
import { env } from './config/env.js'
import { apiRateLimiter } from './middlewares/rateLimiter.js'
import { authenticate } from './middlewares/authenticate.js'
import { apiRouter } from './routes/index.js'
import { notFound } from './middlewares/notFound.js'
import { errorHandler } from './middlewares/errorHandler.js'

export function createApp() {
  const app = express()

  app.use(
    cors({
      origin: env.clientUrl,
      credentials: true,
    }),
  )
  app.use(helmet())
  app.use(morgan('dev'))
  app.use(express.json({ limit: '2mb' }))
  app.use(express.urlencoded({ extended: true }))
  app.use(cookieParser(env.cookieSecret))
  app.use(apiRateLimiter)
  app.use(authenticate)

  const csrfProtection = csurf({ cookie: true })
  app.get('/api/csrf-token', csrfProtection, (req, res) => {
    res.json({ csrfToken: req.csrfToken() })
  })

  app.use('/api', apiRouter)
  app.use(notFound)
  app.use(errorHandler)

  return app
}
