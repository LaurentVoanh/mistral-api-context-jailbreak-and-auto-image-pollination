<?php
session_start();

// Fonction pour générer une couleur sombre aléatoire
function generateRandomDarkColor() {
    $r = mt_rand(0, 64);
    $g = mt_rand(0, 64);
    $b = mt_rand(0, 64);
    return "rgb($r,$g,$b)";
}

$backgroundColor = generateRandomDarkColor();

// Vérifier si l'utilisateur a un cookie d'identification
if (!isset($_COOKIE['user_id'])) {
    // Générer un identifiant unique pour l'utilisateur
    $userId = uniqid();
    // Créer un cookie à vie avec l'identifiant unique
    setcookie('user_id', $userId, time() + (86400 * 365 * 10), "/"); // 10 ans
} else {
    $userId = $_COOKIE['user_id'];
}

// Créer un dossier pour l'utilisateur s'il n'existe pas
$userDir = "user/$userId";
if (!file_exists($userDir)) {
    mkdir($userDir, 0777, true);
}

// Créer un fichier de contexte s'il n'existe pas
$contextFilePath = "$userDir/context.txt";
if (!file_exists($contextFilePath)) {
    file_put_contents($contextFilePath, "");
}

// Créer un fichier d'images s'il n'existe pas
$imagesFilePath = "$userDir/images.txt";
if (!file_exists($imagesFilePath)) {
    file_put_contents($imagesFilePath, "");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Matrix</title>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            background-color: <?php echo $backgroundColor; ?>;
            color: green;
            font-family: 'Courier New', Courier, monospace;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        #chat-container {
            width: 80%;
            max-width: 600px;
            border: 1px solid green;
            padding: 20px;
            border-radius: 10px;
            background-color: black;
            overflow: hidden;
        }

        #chat-box {
            height: 300px;
            overflow-y: scroll;
            border-bottom: 1px solid green;
            margin-bottom: 10px;
            padding-bottom: 10px;
            scroll-behavior: smooth; /* Ajout pour le défilement fluide */

        }

        #user-input {
            width: calc(100% - 70px);
            padding: 10px;
            border: 1px solid green;
            border-radius: 5px;
            background-color: black;
            color: green;
        }

        button {
            padding: 10px;
            border: 1px solid green;
            border-radius: 5px;
            background-color: black;
            color: green;
            cursor: pointer;
        }
        .message {
           display: flex;
           flex-direction: row; /* Afficher en ligne */
           margin-bottom: 20px; /* Marge inférieure pour espacer les bulles */
           opacity: 0;
           transform: translateY(-20px); /* Commencer un peu en haut */
           transition: opacity 0.5s, transform 0.5s;
       }
       .message.visible {
           opacity: 1;
           transform: translateY(0); /* Se déplacer à sa position normale */
       }


       .user-message {
            justify-content: flex-end;
        }

        .ai-message {
            justify-content: flex-start;
        }

       .message .avatar-container {
           width: 50px;
           height: 50px;
           margin: 0 10px;
           border-radius: 50%;
           overflow: hidden;
       }
       .message .avatar-container img{
         width: 100%;
           height: 100%;
           object-fit: cover;
       }
       .message .message-content {
           flex: 1; /* Le contenu prend l'espace disponible */
           padding: 10px 15px;
           border-radius: 10px;
           color:black;
       }
       .user-message .message-content {
            background-color:  #007bff;
           text-align: right;
           
        }
       .ai-message .message-content {
            background-color: #28a745;
           text-align: left;
       }
       .message .message-details {
         font-size: 0.8em;
           margin-top: 5px;
            text-align: right;
       }
       .message .options-container {
         margin-top: 10px;
           text-align: right;
       }
       .message .options-container select {
           padding: 5px;
           border: 1px solid green;
           border-radius: 5px;
           background-color: black;
           color: green;
       }
    </style>
</head>
<body>
    <div id="chat-container">
        <div id="chat-box"></div>
        <input type="text" id="user-input" placeholder="Tapez votre message...">
        <button onclick="sendMessage()">Envoyer</button>
    </div>
    <script>
        function sendMessage() {
            const userInput = document.getElementById('user-input').value;
            const chatBox = document.getElementById('chat-box');

            if (userInput.trim() === '') return;

            // Afficher le message de l'utilisateur
            const userMessage = document.createElement('div');
            userMessage.className = 'message user-message';
            userMessage.innerHTML = `
                <div class="avatar-container">
                  <img src="https://image.pollinations.ai/prompt/photo%20de%20profile%20pour%20un%20utilisateur%20de%20chat?width=50&height=50&nologo=poll&nofeed=yes&seed=<?php echo rand(11111, 99999); ?>" alt="Avatar utilisateur">
                </div>
                <div class="message-content">
                    ${userInput.replace(/\n/g, '<br>')}
                    <div class="message-details">Utilisateur - ${formatDate(new Date())}</div>
                    <div class="options-container">
                       <select class="options-select">
                           <option value="">Options</option>
                       </select>
                    </div>
                </div>
            `;
            chatBox.appendChild(userMessage);
            setTimeout(() => userMessage.classList.add('visible'), 100);
            chatBox.scrollTop = chatBox.scrollHeight;


            // Envoyer la requête à chat.php
            fetch('chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message: userInput })
            })
            .then(response => response.json())
            .then(data => {
                // Afficher la réponse de l'IA
                const aiMessage = document.createElement('div');
                aiMessage.className = 'message ai-message';
                 aiMessage.innerHTML = `
                   <div class="avatar-container">
                        <img src="https://image.pollinations.ai/prompt/photo%20de%20profile%20pour%20un%20assistant%20virtuel%20?width=50&height=50&nologo=poll&nofeed=yes&seed=<?php echo rand(11111, 99999); ?>" alt="Avatar OpenIA">
                   </div>
                   <div class="message-content">
                       ${data.response.replace(/\n/g, '<br>')}
                       <div class="message-details">OpenIA - ${formatDate(new Date())}</div>
                        <div class="options-container">
                           <select class="options-select">
                               <option value="">Options</option>
                           </select>
                        </div>
                   </div>
                 `;
                chatBox.appendChild(aiMessage);
                setTimeout(() => aiMessage.classList.add('visible'), 100);
                chatBox.scrollTop = chatBox.scrollHeight;

                // Stocker la réponse cachée dans le contexte
                fetch('context.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ context: data.hidden, options:data.options })
                });
            })
            .catch(error => console.error('Error:', error));

            // Vider l'input
            document.getElementById('user-input').value = '';
        }


      // Fonction pour formater la date
        function formatDate(date) {
            const days = ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
            const dayName = days[date.getDay()];
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${dayName} ${day} ${month} ${year} ${hours}h${minutes}`;
        }


      // Envoyer le message en appuyant sur Entrée
      document.getElementById('user-input').addEventListener('keypress', function(e) {
          if (e.key === 'Enter') {
              sendMessage();
          }
      });
    </script>
</body>
</html>