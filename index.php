<?php

require 'flight/Flight.php';
require 'class.portal.php';
require ("ESL.php"); 
require ("phpagi/phpagi-asmanager.php");

error_reporting(E_ALL ^ E_DEPRECATED);

Flight::register('db', 'PDO', array(
    'mysql:host=65.48.98.242;dnbname=fsconference',
    'freeswitch',
    'fr33sw1tch'));
Flight::set('flight.handle_errors', true);
//error_reporting(E_ALL & ~ E_NOTICE);

Flight::route('/', function(){
    echo 'hello world!';
});

Flight::route('POST /provisionConference', function () {
    $datao = Flight::request()->data; $data = doArray($datao); echo json_encode(provisionConference("create", $data)); }
);

Flight::route('POST /addUser', function () {
    $datao = Flight::request()->data; $data = doArray($datao); echo json_encode(addUser($data)); }
);

Flight::route('POST /resetUserPassword', function () {
    $datao = Flight::request()->data; $data = doArray($datao); echo json_encode(resetUserPassword($data)); }
);

Flight::route('POST /userLogin', function () {
    $datao = Flight::request()->data; $data = doArray($datao); echo json_encode(userLogin($data['username'], $data['password'])); }
);

Flight::route('GET /checkUser/@user', function ($user) {
    echo json_encode(checkUser($user)); }
);


Flight::route('GET /getUserConferences/@userid', function ($user) {
    echo json_encode(getUserConferences($user)); }
);

Flight::route('GET /provisionConference/@room', function ($room) {
    echo json_encode(getConferencesbyRoom($room)); }
);

Flight::route('POST /updateProvisionConference', function () {
    $datao = Flight::request()->data; $data = doArray($datao); echo json_encode(provisionConference("update", $data)); }
);

Flight::route('/getRecording/@uuid', function ($uuid) {
    echo json_encode(getRecording($uuid)); }
);

Flight::route('/getRecordings/@room', function ($room) {
    echo json_encode(getRecordings($room)); }
);

Flight::route('/doRecording/@method/@room', function ($method, $room) {
    echo json_encode(doRecording($method, $room)); }
);

Flight::route('/greetingRecord/@room/@dnis', function ($room, $dnis) {
    echo json_encode(greetingRecord($room, $dnis)); }
);

Flight::route('/greetingPlayback/@room', function ($room) {
    echo json_encode(greetingPlayback($room)); }
);

Flight::route('/undeafConferenceRoom/@room', function ($room) {
    echo json_encode(undeafConferenceRoom($room)); }
);

Flight::route('/deafConferenceRoom/@room', function ($room) {
    echo json_encode(deafConferenceRoom($room)); }
);

Flight::route('/getConferenceRoomInfo/@room', function ($room) {
    echo json_encode(getConferenceRoomInfo($room)); }
);

Flight::route('/lockConferenceRoom/@room', function ($room) {
    echo json_encode(lockConferenceRoom($room)); }
);

Flight::route('/unlockConferenceRoom/@room', function ($room) {
    echo json_encode(unlockConferenceRoom($room)); }
);

Flight::route('/dial/@room/@dnis/@ani', function ($room, $dnis, $sni) {
    echo json_encode(dial($room, $dnis, $ani)); }
);

Flight::route('/muteConferenceRoom/@room', function ($room) {
    echo json_encode(muteConferenceRoom($room)); }
);

Flight::route('/unmuteConferenceRoom/@room', function ($room) {
    echo json_encode(unmuteConferenceRoom($room)); }
);

Flight::route('/toggleMuteConferenceUser/@room/@uuid', function ($room, $uuid) {
    echo json_encode(toggleMuteConferenceUser($room, $uuid)); }
);

Flight::route('/undeafConferenceUser/@room/@uuid', function ($room, $uuid) {
    echo json_encode(undeafConferenceUser($room, $uuid)); }
);

Flight::route('/deafConferenceUser/@room/@uuid', function ($room, $uuid) {
    echo json_encode(deafConferenceUser($room, $uuid)); }
);

Flight::route('/getBridges/@custid', function ($custid) {
    echo json_encode(getBridges($custid)); }
);

Flight::route('/getAllConferenceRooms/@custid', function ($custid) {
    echo json_encode(getAllConferenceRooms($custid)); }
);

Flight::route('/getConferences/@dnis', function ($dnis) {
    echo json_encode(getConferencesbyDNIS($dnis)); }
);

Flight::route('DELETE /delUser/@email', function ($email) {
    echo json_encode(delUser($email)); }
);

Flight::route('DELETE /delConference/@confid', function ($confid) {
    echo json_encode(delConference($confid)); }
);

Flight::route('DELETE /delRecording/@uuid', function ($uuid) {
    echo json_encode(delRecording($uuid)); }
);

function doArray($object) {
    $post = array();
    foreach ($object as $key => $value) {
        $post[$key] = $value;
    }
    return $post;
}


Flight::start();

?>
