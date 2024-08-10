<?php
include "settings.php";
if(isset($_GET["page"])){
    $page = $_GET["page"];
    $filtered_page = str_replace([".","\\",","], "", $page);
    if(str_ends_with($filtered_page,"/")){
        $filtered_page = $filtered_page . "main";
    }
}
else{
    $filtered_page = "main";
}
//Cleanup of string
$before = str_replace("\n", "\\n", file_get_contents("$folder/" . $filtered_page . ".md"));
$output = str_replace('"', '\"', $before);
function get_dir($path) {
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

    $files = array(); 
    foreach ($rii as $file){
        if (!$file->isDir()){
            $files[] = $file->getPathname();
        }
    }

    return $files;
}

function make_tree_view(){
    include "settings.php";
    //Get all files in the directory
    $arr = get_dir("$folder/");
    //Make an array that will store all of the directories with files
    $tree_arrays = [];

    //Go though all files and folders
    foreach($arr as &$value){
        //Make Sure the file doesnt have any extension, and the folder gets stripped
        $val = str_replace(["$folder\\",".md"],"", $value);
        //Split the string by the slashes, to filter out directories
        $val_tree = explode("\\",$val);
        //Check if the file is not in a directory
        if(count($val_tree)==1){
            if($val == "main"){
                echo("<li><a href='?page=$val'>$name_of_root_page</a></li>");
                continue;
            }
            echo("<li><a href='?page=$val'>$val</a></li>");
        }else{
            //If the file is in a dir, check if another file added the dir to the array, otherwise add it and the file
            if(!array_key_exists($val_tree[0], $tree_arrays)){
                $tree_arrays[$val_tree[0]] = array(); 
            }
            $tree_arrays[$val_tree[0]][] = $val_tree[1];
        }
    }
    // Cleanup
    unset($value);
    unset($val);
    unset($val_tree);

    //Loop Through all of the directories
    foreach(array_keys($tree_arrays) as &$dir){
        echo("<li><span class='nest'><a href='?page=$dir/'>$dir</a></span><ul class='nested'>");
        //Add all of the files in the directories
        foreach($tree_arrays[$dir] as &$file){
            if($file == "main"){
                continue;
            }
            echo("<li><a href='?page=$dir/$file'>$file</a></li>");
        }
        echo("</ul></li>");
    }

    //Cleanup
    unset($file);
    unset($dir);
}


?>

<html>
    <head>
        <title>Docs Website</title>
        <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
        <link rel="stylesheet" type="text/css" href="index.css">
    </head>
    <body>
        <ul class="info">
            <pre><?php make_tree_view() ?></pre>
        </ul>
        <div class="column">
            <main id="content"></main>
            <footer id="footer"></footer>
            <script>
                document.getElementById('content').innerHTML = marked.parse("<?php echo($output); ?>");
            </script>
            <?php
                if(file_exists("$folder/$footer.md")){
                    //More String Cleanup for the Footer
                    $f_before = str_replace("\n","\\n", file_get_contents("$folder/$footer.md"));
                    $f = str_replace('"', '\"', $f_before);
                    echo("<script>document.getElementById('footer').innerHTML = marked.parse(\"".$f."\");</script>");
                }
            ?>
        </div>
    </body>

    <script>
var toggler = document.getElementsByClassName("nest");
var i;

for (i = 0; i < toggler.length; i++) {
  toggler[i].addEventListener("click", function() {
    this.parentElement.querySelector(".nested").classList.toggle("active");
    this.classList.toggle("nest-down");
  });
}
    </script>
</html>