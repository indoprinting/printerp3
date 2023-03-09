const express = require('express');
const app = express();
const { createServer } = require('http');
const server = createServer(app);
const { Server } = require('socket.io');
const io = new Server(server);

app.get('/', (req, res) => {
  res.sendFile(__dirname + '/index.html');
});

app.use(express.static('./'));

io.on('connection', (socket) => {
  console.log('a user connected');

  socket.emit('message', 'Hello');
  
  socket.on('message', (msg) => {
    console.log(msg);
  });
});

server.listen(3000, () => {
  console.log('listening on *:3000');
});