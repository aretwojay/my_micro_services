const Discussion = require('../model/discussion.model.js')
const axios = require('axios');

const getDiscussions = (async (req, res) => {
    let discussion = await Discussion.find();
    res.status(200).json(discussion);
})

const getDiscussion = (async (req, res) => {
    const id = req.params.id;
    try {
        const discussion = await Discussion.findById(id)
        res.status(200).json(discussion)
    }
    catch (err) {
        return res.status(404).send('Discussion not found')
    }
})

const createDiscussion = (async (req, res) => {
    let users = req.body.users;
    let messages = req.body.message;
    let checkUsers = users?.map((id) => {
        return axios.get("http://localhost:8080/api/user/" + id, { headers: { "Authorization": req.headers.authorization } }).then((res) => {
            return res.data
        });
    })
    if (checkUsers === undefined) {
        return res.status(400).send("Users data not found")
    }
    Promise.all(checkUsers).then(async (promise) => {
        const errorResponse = promise.find((response) => response === null);
        const errorTokenNotFound = promise.find((response) => response?.success === false);
        if (errorResponse === null) {
            res.status(400).send("User not found")
        }
        else if (errorTokenNotFound?.success === false) {
            res.status(400).send(errorTokenNotFound.message)
        }
        else {
            try {
                let newDiscussion = await Discussion.create({ users, messages })
                res.status(201).json(newDiscussion)
            }
            catch (err) {
                res.status(400).send('Discussion creation went wrong')
            }
        }
    })
})

const addMessage = (async (req, res) => {
    const id = req.params.id;
    let users = [req.body?.sender]
    if (req.body?.receiver) {
        users.push(req.body?.receiver);
    }
    let checkUsers = users?.map((id) => {
        return axios.get("http://localhost:8080/api/user/" + id, { headers: { "Authorization": req.headers.authorization } }).then((res) => {
            return res.data
        });
    })
    if (checkUsers === undefined) {
        return res.status(400).send("Users data not found")
    }
    Promise.all(checkUsers).then(async (promise) => {
        console.log(promise)
        const errorResponse = promise.find((response) => response === null);
        const errorTokenNotFound = promise.find((response) => response?.success === false);
        if (errorResponse === null) {
            res.status(400).send("User not found")
        }
        else if (errorTokenNotFound?.success === false) {
            res.status(400).send(errorTokenNotFound.message)
        }
        else {
            try {
                const messageAdded = await axios.post("http://localhost:8080/api/message",
                    { content: req.body.content, sender: req.body.sender },
                    { headers: { "Authorization": req.headers.authorization } }).then((res) => {
                        return res.data
                    });
                if (messageAdded?.success === false) {
                    res.status(400).send(messageAdded?.message)
                }
                else {
                    let updatedDiscussion = await Discussion.findByIdAndUpdate(id, { $push: { messages: messageAdded?.message?.id } }, { new: true })
                    res.status(200).send(messageAdded)
                }
            }
            catch (err) {
                res.status(400).send('Discussion creation went wrong' + err)
            }
        }
    })

})

const updateDiscussion = (async (req, res) => {
    const id = req.params.id;
    let users = req.body.users;
    console.log(users)
    let checkUsers = users?.map((id) => {
        return axios.get("http://localhost:8080/api/user/" + id, { headers: { "Authorization": req.headers.authorization } }).then((res) => {
            return res.data
        });
    })
    if (checkUsers === undefined) {
        return res.status(400).send("Users data not found")
    }
    Promise.all(checkUsers).then(async (promise) => {
        const errorResponse = promise.find((response) => response === null);
        const errorTokenNotFound = promise.find((response) => response?.success === false);
        if (errorResponse === null) {
            res.status(400).send("User not found")
        }
        else if (errorTokenNotFound?.success === false) {
            res.status(400).send(errorTokenNotFound.message)
        } else {
            try {
                let updatedDiscussion = await Discussion.findByIdAndUpdate(id, { users }, { new: true })
                res.status(201).json(updatedDiscussion)
            }
            catch (err) {
                res.status(404).send('Discussion update went wrong')
            }
        }
    })

})


const deleteDiscussion = (async (req, res) => {
    const id = req.params.id;
    try {
        let deletedDiscussion = await Discussion.deleteOne({ _id: id });
        res.status(201).json(deletedDiscussion)
    }
    catch (err) {
        res.status(404).send('Discussion deletion went wrong')
    }
})

module.exports = {
    getDiscussions,
    getDiscussion,
    createDiscussion,
    addMessage,
    updateDiscussion,
    deleteDiscussion
}