import http from 'http'
import { createApp } from './app.js'
import { env } from './config/env.js'
import { setupSocketServer } from './sockets/index.js'

const app = createApp()
const server = http.createServer(app)

setupSocketServer(server)

server.listen(env.port, () => {
  console.log(`Server listening on port ${env.port}`)
})
