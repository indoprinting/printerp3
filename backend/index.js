const express = require('express');
const fs = require('fs');
const https = require('https');
const mysql = require('mysql');
const { Server } = require('socket.io');

const PORT = 3000;

let option = {
  cert: fs.readFileSync('/www/server/panel/vhost/cert/erp.indoprinting.co.id/fullchain.pem'),
  key: fs.readFileSync('/www/server/panel/vhost/cert/erp.indoprinting.co.id/privkey.pem')
};

var dbConnection = mysql.createConnection({
  host: 'localhost',
  user: 'idp_erp',
  password: 'Dur14n100$',
  database: 'idp_erp'
});

let app = express();
let httpsServer = https.createServer(option, app);
const io = new Server(httpsServer, {
  cors: {
    origin: 'https://erp.indoprinting.co.id'
  }
});

app.get('/', (req, res) => {
  console.log('New request.');
  if (req.query?.notify && req.query.notify == 1) {
    console.log('Send new notification');
    io.emit('notify', 'You have new notification');
  }

  res.writeHead(200);
  res.end('OK');
});

io.on('connection', (socket) => {
  socket.emit('session', { user_id: socket.userId });

  socket.on('disconnect', (socket) => {
    dbConnection.connect();
    dbConnection.query(`UPDATE users SET socket_id = NULL WHERE socket_id = '${socket.id}'`, () => {
      dbConnection.end();
    });
  });
});

httpsServer.listen(PORT, () => {
  console.log('PrintERP3 Backend server is running on port ' + PORT);
});
