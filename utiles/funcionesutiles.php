<?php
    function formatoRUTValido($rut) {
        // Eliminar caracteres no válidos
        $rut = preg_replace('/[^kK0-9]/', '', $rut); 
        if (empty($rut)) {
            return false;
        }

        // Obtener los números del RUT y el dígito verificador
        $rutNumeros = substr($rut, 0, strlen($rut)-1);
        $dv = strtoupper(substr($rut, -1));

        // Validar si el dígito verificador es K o número
        if ($dv != 'K' && !is_numeric($dv)) {
            return false;
        }

        // Cálculo del dígito verificador
        $i = 2;
        $suma = 0;
        foreach(array_reverse(str_split($rutNumeros)) as $v) {
            if ($i == 8) {
                $i = 2;
            }            
            $suma += $v * $i;
            ++$i;
        }
        $digitoVerificadorCalculado = 11 - ($suma % 11);

        // Comparar dígito verificador calculado con el ingresado (considerando K como 10)
        if ($digitoVerificadorCalculado == 11 && ($dv == '0')) {
            return true;
        } elseif ($digitoVerificadorCalculado == 10 && $dv == 'K') {
            return true;
        } elseif ($digitoVerificadorCalculado == intval($dv)) {
            return true;
        } else {
            return false;
        } 
    }
    function correccion_tildes($text) {
        // Mapeo de caracteres con tilde a sus versiones sin tilde
        $replace_map = array(
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
        );
        
        // Reemplazar los caracteres con tilde por sus versiones sin tilde
        $updated_text = strtr($text, $replace_map);
        return $updated_text;
    }
?>