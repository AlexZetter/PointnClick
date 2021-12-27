<?php 
session_start();
require_once "functions.php";
$rqstMethod = $_SERVER["REQUEST_METHOD"];

if ($rqstMethod === "POST"){
    
           //skapar en NY användare
        //input:
        //{
        //   "nameTag": "string",
        //   "password": "string" 
        //}
        //output:
        //{
        //    "
        //}

        if (isset($_POST["nameTag"], $_POST["password"], $_FILES["image"])) {
            $nameTag = $_POST["nameTag"];
            $password = $_POST["password"];

            //variabler för bild-filen
            $profilePicture = $_FILES["image"];
            $filename = $profilePicture["name"];
            $tempname = $profilePicture["tmp_name"];
            $size = $profilePicture["size"];
            $error = $profilePicture["error"];

            //nameTag är färre än 3 bokstäver
            if (strlen($nameTag) <= 2) {
                sendJson(["Please add more characters to your nameTag."], 406);
            }
            //lösenord är färre än 4 bokstäver
            if (strlen($password) <= 3) {
                sendJson(["Please add more characters to your password."], 406);
                if (preg_match('~[0-9]+~', $password)) {
                    sendJson(["Your password has to at least include one number."], 406 );
                    exit();
                }
            }
            //hantering för bild som användaren laddar upp
            if ($error !== 0) {
                sendJson(["Something went wrong with the picture, try again."], 409 );
                exit();
            }
                // Filen får inte vara större än ca 500kb
            if ($size > (0.5 * 1000 * 1000)) {
                sendJson(["Picture too large! Try something smaller than 400kb."], 405) ;
                exit();
            }

            // Hämta filinformation
            $info = pathinfo($filename);
            // Hämta ut filändelsen (och gör om till gemener)
            $ext = strtolower($info["extension"]);
            
            // Konvertera från int (siffra) till en sträng,
            // så vi kan slå samman dom nedan.
            $time = (string) time(); // Klockslaget i millisekunder
            // Skapa ett unikt filnamn med TID + FILNAMN
            $uniqueFilename = sha1("$time$filename");
            // Skickar iväg bilden till vår mapp"
            move_uploaded_file($tempname, "api/profileImages/$uniqueFilename.$ext");

            //när all info har kikats genom och kontrollerats, ska 
            //det läggas till i databasen. 

            //id till ny användare.
            $allUsers = loadJson("api/user.json");
            $highestID = theHighestId($allUsers);

            //ny array med nycklar.
            $newUser = [];
            $newUser["id"] = $highestID;
            $newUser["nameTag"] = $nameTag;
            $newUser["password"] = $password;
            $newUser["profilePicture"] = "$uniqueFilename.$ext";
            $newUser["inventory"] = [];

            $found = false;

            foreach ($users as $key => $user) {
                if ($user["nameTag"] == $rqstData["nameTag"] && $user["password"] == $rqstData["password"]) {
                    $_SESSION["userID"] = $user["id"];
                    $_SESSION["nameTag"] = $user["nameTag"];
                    $_SESSION["isLoggedIn"] = true;
                    $found = true;
                }
            }
            if ($found) {
                sendJson("Login succcessful");
            } else {
                sendJson("Information incorrect", 400);
            }

            //sparar i array, och sen i json-fil.
            array_push($allUsers, $newUser);
            saveJson("api/user.json", $allUsers);
            //sendJson(["User is added."]);
            header("Location: index.php");
            exit();
        } else {
            sendJson(["TagName or Password is not set."], 405);
            exit();
        }
}

?>
