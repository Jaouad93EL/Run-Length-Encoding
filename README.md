# Groupe de elhorm_j

Pour lancer le programe il faut lancer dans src le fichier test.php:

php test.php

exemple:

    encode_rle("AAABBBBCC") => 3A4B2C
    decode_rle("3A4B2C") => AAABBBBCC


    Pour les advanced mettre un bmp dans /tmp qui se nome toto.bmp par exemple champigris comme dans la video de cours mais le renomer en toto.bmp
    
    encode_advanced_rle("/tmp/toto.bmp", "/tmp/toto") => path du fichier
    decode_advanced_rle("/tmp/toto", "/tmp/toto.bmp") => path du fichier



