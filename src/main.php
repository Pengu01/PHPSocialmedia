<!DOCTYPE html>
<html>
<head>
    <title>Chrip</title>
</head>
<body>
    <h1>Chrip</h1>
    <hr>
    <?php
        // Establish database connection
        $db = new SQLite3('question.sq3');
        //Create table for messages
        $db->exec("DROP TABLE users");
        $db->exec("DROP TABLE messages");
        $db->exec("CREATE TABLE IF NOT EXISTS messages(content text, timestamp text, userid text);");
        $db->exec("insert into messages values (\"hejsa\",\"". date('Y-m-j H:m') . "\", \"0\");");
        //Create table for users
        $db->exec("CREATE TABLE IF NOT EXISTS users(username text, id text, email text, password text);");
        $db->exec("insert into users values (\"Alfons\", \"0\", \"alf.stoltz\", \"alf.stoltz\");");
        //ifall du skrivit in en text

        // Retrieve and display messages from the database
        $allInputQuery = "SELECT messages.content, users.username, messages.timestamp FROM messages JOIN users ON messages.userid = users.id ORDER BY messages.timestamp DESC"; 
        $messages = $db->query($allInputQuery); 
        while($row = $messages->fetchArray(SQLITE3_ASSOC))
        {
            echo "<b>" . $row['username'] . ":</b> " . $row['content'] . "<br>";
            echo "<small>" . $row['timestamp'] . "</small>";
            echo "<hr>";
        }
    ?>
    <hr>
    <h3>Chrip a message:</h3>
    <form method="post" action="#">
        <textarea name="content" rows="4" cols="50"></textarea><br>
        <input type="submit" value="Post">
    </form>
</body>
</html>