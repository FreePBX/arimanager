/**
 * The goal is to have a universal router. This will ask your module what to do, your module will do it.
 * 
 * We need an object
 * {
 *      'status': bool,
 *      'actions' : [actionOBJ],
 *      'message': string
 * }
 * 
 * actonOBJ: { 'action' : string, 'data': actiondata}
 * 
 * 
 */

const FreePBX = new require("freepbx");
const ARI = require('ari-client');
const appname = 'ARIRouter';
var maxConcurrent = 1;
var maxQueue = Infinity;
var queue = new Queue(maxConcurrent, maxQueue);
//Connection events
FreePBX.connect()
    .then(freepbx => {
        return ARI.connect('https://localhost:8088', freepbx.config.FPBX_ARI_USER, freepbx.config.FPBX_ARI_PASSWORD)
    })
    .then(ari => {
        let channels = ari.channels;
        let bridges = ari.bridges;
        let endpoints = ari.endpoints;
        ari.on('StasisStart', function (event, channelInstance) {
            let component = event.args.component;
            let command = event.args.command;
            doTheThing(component, command, channelInstance, ari);
        });
        ari.start(appname);
    })
    .catch(err => {
        console.log(err);
    });


function doTheThing(component, command, channel, ari){
    var request = require('request');
    request.post(url).form(channel).then();

}

//Event says map the channsle somewhere
function handleRedirectEvent(data){}
//Event says play audio
function handlePlaybackEvent(data){}
//Event says terminate channel
function handleDisconnectEvent(data){}
//Event says get user input, return and get new event queue....
function handleFeedbackEvent(data){}