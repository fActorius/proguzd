<?php
 //prisijungimas prie duombazes
$DATABASE_HOST = '127.0.0.1:3306';
$DATABASE_USER = 'u113585244_vartotojas';
$DATABASE_PASS = 'Duomenubaze123';
$DATABASE_NAME = 'u113585244_gediminobaz';
try {
    $pdo = new PDO('mysql:host=' . $DATABASE_HOST . ';dbname=' . $DATABASE_NAME . ';charset=utf8', $DATABASE_USER, $DATABASE_PASS);
    } 
    catch (PDOException $exception) {
        exit('Failed to connect to database!');
    }

    function show_comments($comments, $parent_id = -1) {
        $html = '';
        if ($parent_id != -1) {
            array_multisort(array_column($comments, 'submit_date'), SORT_ASC, $comments);
        }
        foreach ($comments as $comment) {
            if ($comment['parent_id'] == $parent_id) {
                $html .= '
                    <div class="comment">
                        <div>
                            <h3 class="name">'  . htmlspecialchars($comment['name'],    ENT_QUOTES) . '</h3>
                            <h3 class="email">' . htmlspecialchars($comment['email'],   ENT_QUOTES) . '</h3>
                            <span class="date">' . $comment['submit_date'] . '</span>
                        </div>
                        <div class="contentbar">
                            <p class="content">'    . nl2br(htmlspecialchars($comment['content'], ENT_QUOTES)) . '</p>
                        </div>
                        <a class="reply_comment_btn" alt="" href="#" data-comment-id="' . $comment['id'] . '">Reply</a>
                        ' . show_write_comment_form($comment['id']) . '
                        <div class="replies">
                            ' . show_comments($comments, $comment['id']) . '
                        </div>
                    </div>
                ';
            }
        }
    return $html;
    }
    function show_write_comment_form($parent_id = -1) {
        $html = '
            <div class="write_comment" data-comment-id="' . $parent_id . '">
                <form id="myForm">
                    <input name="parent_id" type="hidden" value="' . $parent_id . '">
                    <input name="name" type="text" placeholder="Your Name" required>
                    <input name="email" type="email" placeholder="Your Email" required>
                    <textarea name="content" placeholder="Write your comment here..." required></textarea>
                    <button type="submit">Submit Comment</button>
                </form>
            </div>
        ';
    return $html;
    }
    if (isset($_GET['page_id'])) {
        if (isset($_POST['email'],$_POST['name'], $_POST['content'])) {
            $stmt = $pdo->prepare('INSERT INTO comments (page_id, parent_id, email, name, content, submit_date) VALUES (?,?,?,?,?,NOW())');
            $stmt->execute([ $_GET['page_id'], $_POST['parent_id'], $_POST['email'], $_POST['name'], $_POST['content'] ]);
            exit('Your comment has been submitted!');
        }
        $stmt = $pdo->prepare('SELECT * FROM comments WHERE page_id = ? ORDER BY submit_date DESC');
        $stmt->execute([ $_GET['page_id'] ]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $pdo->prepare('SELECT COUNT(*) AS total_comments FROM comments WHERE page_id = ?');
        $stmt->execute([ $_GET['page_id'] ]);
        $comments_info = $stmt->fetch(PDO::FETCH_ASSOC);
        } 
        else {
        exit('No page ID specified!');
        }
?>
    <div class="comment_header">
        <span class="total"><?=$comments_info['total_comments']?> comments</span>
        <a href="#" class="write_comment_btn" data-comment-id="-1">Write Comment</a>   
    </div>

<?=show_write_comment_form()?>
<?=show_comments($comments)?>
