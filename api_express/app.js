const express = require('express');
const bodyParser = require('body-parser');
const mongoose = require('mongoose');
const { getDiscussions, getDiscussion, createDiscussion, updateDiscussion, deleteDiscussion, addMessage } = require('./controller/discussion.controller');
// init app
const app = express();
// connect MongoDB with mongoose
let dev_db_url = 'mongodb://127.0.0.1:27017/my_micro_services?directConnection=true&serverSelectionTimeoutMS=2000';
let mongoDB = process.env.MONGODB_URI || dev_db_url;
mongoose.connect(mongoDB);
mongoose.Promise = global.Promise;
let db = mongoose.connection;
db.on('error ', console.error.bind(console, 'Connexion error on MongoDB : '));
// Utilisation de body parser
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: false }));
let port = 5555;
app.listen(
    port, () => {
        console.log(' Server running on : ' + port);
    }
);

app.get("/api/discussion", getDiscussions);
app.get("/api/discussion/:id", getDiscussion);
app.post("/api/discussion/", createDiscussion);
app.post("/api/discussion/:id", addMessage);
app.put("/api/discussion/:id", updateDiscussion);
app.delete("/api/discussion/:id", deleteDiscussion);
