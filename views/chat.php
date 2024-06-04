<?php
require_once 'layout.php';
require_once '../controllers/ChatController.php';
$controller = new ChatController();

$pengirim = isset($_GET['pengirim']) ? $_GET['pengirim'] : '';
$penerima = isset($_GET['penerima']) ? $_GET['penerima'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pengirim']) && isset($_POST['penerima']) && isset($_POST['pesan'])) {
    $controller->kirimPesan();
    header('Location: ' . $_SERVER['PHP_SELF'] . '?pengirim=' . $pengirim . '&penerima=' . $penerima);
    exit();
}

$pesan = $controller->model->getPesan($pengirim, $penerima);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        #chat-toggle {
            background-color: white;
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            width: 60px;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div id="chat-toggle" class="fixed bottom-4 left-4 cursor-pointer">
        <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
    </div>

    <div id="chat-box" class="fixed bottom-4 right-1/2 translate-x-1/2 bg-white shadow-md rounded-lg max-w-md mx-auto">
        <div class="bg-gray-200 p-2 rounded-t-lg flex items-center">
            <div class="w-8 h-8 bg-gray-300 rounded-full mr-2"></div>
            <h3 class="text-lg font-semibold flex-grow"><?php echo $penerima; ?></h3>
            <div class="w-6 h-6 bg-gray-300 rounded-full ml-2"></div>
        </div>
        <div class="chat-body p-2">
            <?php foreach ($pesan as $p): ?>
                <div class="flex mb-2 <?php echo ($p['pengirim'] == $pengirim) ? 'justify-end' : ''; ?>">
                    <div class="<?php echo ($p['pengirim'] == $pengirim) ? 'bg-blue-500 text-white' : 'bg-gray-200'; ?> p-2 rounded-lg max-w-xs">
                        <p><?php echo $p['pesan']; ?></p>
                        <small class="text-xs"><?php echo $p['waktu']; ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="bg-gray-200 p-2 rounded-b-lg flex">
            <form method="post" action="<?php echo $_SERVER['PHP_SELF'] . '?pengirim=' . $pengirim . '&penerima=' . $penerima; ?>" class="flex-grow flex">
                <input type="hidden" name="pengirim" value="<?php echo $pengirim; ?>">
                <input type="hidden" name="penerima" value="<?php echo $penerima; ?>">
                <input type="text" name="pesan" placeholder="Tulis pesan..." required class="flex-grow px-2 py-1 rounded-l-md">
                <button type="submit" class="bg-blue-500 text-white px-4 py-1 rounded-r-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <script>
        var lastMessageId = 0;
        var pengirim = '<?php echo $pengirim; ?>';
        var penerima = '<?php echo $penerima; ?>';

        function getNewMessages() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_new_messages.php?pengirim=' + pengirim + '&penerima=' + penerima + '&lastId=' + lastMessageId, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                    var messages = JSON.parse(xhr.responseText);
                    for (var i = 0; i < messages.length; i++) {
                        var message = messages[i];
                        lastMessageId = Math.max(lastMessageId, message.id);
                        addMessageToChat(message.pengirim, message.pesan, message.waktu);
                    }
                }
            };
            xhr.send();
        }

        function addMessageToChat(pengirim, pesan, waktu) {
            var chatBody = document.querySelector('.chat-body');
            var pesanElement = document.createElement('div');
            pesanElement.classList.add('pesan');
            pesanElement.innerHTML = `
                <strong>${pengirim}</strong>
                <p>${pesan}</p>
                <small>${waktu}</small>
            `;
            chatBody.appendChild(pesanElement);
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        setInterval(getNewMessages, 500); 

        const chatToggle = document.getElementById('chat-toggle');
        const chatBox = document.getElementById('chat-box');

        chatToggle.addEventListener('click', function() {
            chatBox.classList.toggle('hidden');
        });
    </script>
</body>
</html>