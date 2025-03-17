<?php
include "config/database.php";
if ($conn) {
    echo "✅ Database Connected Successfully!";
} else {
    echo "❌ Database Connection Failed!";
}
?>
