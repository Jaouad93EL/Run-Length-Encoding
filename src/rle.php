<?php

function encode_rle(string $str) {

    if (empty($str) || !ctype_alpha($str)) /*verifier si la chaine est empty ou si contient des carac non alpha*/
        return "$$$";
    
    $encode = "";
    $nb = 1;
    $i = 1;

    /*parcours la chaine en comptent le nombre d'occurence 
    des qu'il ny a plus d'occurence on concatene le nombre d'occurence et le caractère */
    while ($i < strlen($str)) {
        if ($str[$i] == $str[$i - 1])
            $nb++;
        else {
            $encode .= strval($nb) . $str[$i -1];
            $nb = 1;
        }
        $i++;
    }
    $encode .= strval($nb) . $str[$i - 1];
    return $encode;
}

function decode_rle(string $str) {

    if (empty($str) || $str == "$$$")
        return "$$$";

    $nb_stock = "";
    $decode = "";
    
    /*parcours la chaine si c'est un chiffre on le concatene jusqua ne plus d'avoir de chiffre 
    et ensuite on boucle le chiffre en ajoutant le caractère pour revenir à l'état initial*/
    for ($i = 0; $i < strlen($str); $i++) {
        if (is_numeric($str[$i]))
            $nb_stock .= $str[$i];
        else {
            for ($j = 0; $j < intval($nb_stock); $j++)
                $decode .= $str[$i];
            $nb_stock = "";
        }
    }
    return $decode;
}

function read_file(string $path_to_encode) {
    $handle = fopen($path_to_encode, 'r');
    if (filesize($path_to_encode) < 4)
        return -1;
    $data = fread($handle, filesize($path_to_encode));
     /* verifie si le fichier est un bmp si oui on le convertie en hexa*/
    if (substr($path_to_encode, -4) == ".bmp" || substr($path_to_encode, -4) == ".BMP")
        $data = bin2hex($data);
    fclose($handle);
    return ($data);
}

function write_file(string $result_path, string $encode) {
    $handle = fopen($result_path, 'w') or die('Cannot open file:  '.$result_path);
    /* verifie si le fichier est un bmp si oui on le convertie en bin*/
    if (substr($result_path, -4) == ".bmp" || substr($result_path, -4) == ".BMP")
        $encode = hex2bin($encode);
    fwrite($handle, $encode);
    fclose($handle);
}

function encode_advanced_rle(string $path_to_encode, string $result_path) {
    $string = read_file($path_to_encode);
    if ($string == -1)
        return "$$$";
    $i = 2;
    $encode = "";
    $string_one = $string[$i] . $string[$i + 1];
    $string_two = $string[$i - 2] . $string[$i - 1];

    while ($i <= strlen($string) - 2) {
        $sub_str = "";
        $nb = 0;
        /*si c'est une occurence on boucle tant qu'il y a des occurences, 
        que nb ne depasse pas dizaines et tant qu'on est pas a la fin de la chaine */
        if ($string_one == $string_two) {
            $nb++;
            while ($string_one == $string_two && $nb < 99 && $i <= strlen($string) - 2) {
                $nb++;
                $i += 2;
                if ($i <= strlen($string) - 2) {
                    $string_one = $string[$i] . $string[$i + 1];
                    $string_two = $string[$i - 2] . $string[$i - 1]; 
                }
            }
            /*si il y a plus de 10 occurences on concatene sinon on rajoute un 0 en unité*/
            if ($nb >= 10)
                $sub_str = strval($nb) . $string_two;
            else
                $sub_str = "0" . strval($nb) . $string_two;
                /*si le nombre d'occurences est egale a 99 donc juste avant de passé au centaines
                 on avance dans la chaine de caractere en incrémentant $i*/
            if ($nb == 99)
                $i += 2;
        }
        /*si c'est différent on boucle tant que c'est différent, 
        que nb ne depasse pas dizaines et tant qu'on est pas a la fin de la chaine */
        else if ($string_one != $string_two) {
            if ($i == 2) {
                $sub_str .= $string_two;
                $nb++;
            }
            while ($string_one != $string_two && $nb < 99 && $i <= strlen($string) - 2) {
                if ($i <= strlen($string) - 4)
                    if ($string_one != ($string[$i + 2] . $string[$i + 3])) {
                        $sub_str .= $string_one;
                        $nb++;
                    }
                if ($i == strlen($string) - 2) {
                    $nb++;
                    $sub_str .= $string_one;
                }
                $i += 2;
                if ($i <= strlen($string) - 2) {
                    $string_one = $string[$i] . $string[$i + 1];
                    $string_two = $string[$i - 2] . $string[$i - 1]; 
                }
            }
            /*si il y a plus de 10 occurences on concatene sinon on rajoute un 0 en unité
            si il n'y a pas d'occurences on ajoute rien*/
            if ($nb >= 10)
                $sub_str = strval($nb) . $sub_str;
            else if ($nb == 0)
                $sub_str = "";
            else
                $sub_str = "0" . strval($nb) . $sub_str;
            if ($nb > 1)
                $sub_str = "00" . $sub_str;
        }
        $encode .= $sub_str;
    }
    write_file($result_path, $encode . "0000");
    return realpath($result_path);
}

function decode_advanced_rle(string $path_to_encode, string $result_path) {
    $string = read_file($path_to_encode);
    $i = 2;
    $decode = "";

    while ($i <= strlen($string) - 4) {
        $string_one = $string[$i] . $string[$i + 1];
        $string_two = $string[$i - 2] . $string[$i - 1];
        /*si string two égale au caractère de contrôle 
        et que string one est un chiffre on boucle les caractères différents*/
        if ($string_two == "00" && is_numeric($string_one)) {
            $sub_str = "";
            for ($j = 0; $j < intval($string_one); $j++) {
                $i += 2;
                $sub_str .= $string[$i] . $string[$i + 1];
            }
            $decode .= $sub_str;
        }
        /*si string two est différent du caractère de contrôle 
        et que string one est un chiffre on boucle les occurrences*/
        else if ($string_two != "00" && is_numeric($string_two)) {
            $sub_str = "";
            for ($j = 0; $j < intval($string_two); $j++) {
                $sub_str .= $string[$i] . $string[$i + 1];
            }
            $decode .= $sub_str;
        }
        $i += 4;
    }
    write_file($result_path, $decode);
    return realpath($result_path);
}
?>