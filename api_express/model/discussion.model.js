const mongoose = require('mongoose');

const discussionSchema = mongoose.Schema({
    users: { type: Array, required: true },
    messages: { type: Array, required: false },
    createdAt: { type: Date, default: Date.now, required: true },
}, { collection: 'discussion' });

module.exports = mongoose.model('discussion', discussionSchema);