<?
/*
	This PHP script is licensed under the GPL

	Author: Terence Yim
	E-mail: chtyim@gmail.com
	Homepage: http://web-news.sourceforge.net
*/
	function uudecode($in) {
		$out = "";
		
		$lines = preg_split("/\r?\n/", $in);

		foreach ($lines as $line) {
			$len = ord($line{0});
			if (($len < 0x20) || ($len > 0x5f)) {
				break;
			}				
			$len = $len - 0x20;
			$temp = $len;
			$new_len = strlen($out) + $len;
			
			$i = 1;
			$tmp_out = "";
			while ($temp > 0) {
				$tmp_out .= chr(((ord($line{$i}) - 0x20) << 2) & 0xFC | ((ord($line{$i + 1}) - 0x20) >> 4) & 0x03);
				$tmp_out .= chr(((ord($line{$i + 1}) - 0x20) << 4) & 0xF0 | ((ord($line{$i + 2}) - 0x20) >> 2) & 0x0F);
				$tmp_out .= chr(((ord($line{$i + 2}) - 0x20) << 6) & 0xC0 | (ord($line{$i + 3}) - 0x20) & 0x3F);

				$temp -= 3;
				$i += 4;
			}
			$out .= substr($tmp_out, 0, $len);
			
			$count++;
		}
		
		return $out;
	}
	
	
	function uudecode_output($in) {
		$in_len = strlen($in);
		$offset = 0;
		
		while ($offset < $in_len) {
			$len = ord($in{$offset});
			if (($len < 0x20) || ($len > 0x5f)) {
				break;	// Decode done
			}
			$len = $len - 0x20;
			$temp = $len;

			$out = "";
			$i = $offset + 1;
			while ($temp > 0) {
				$out .= (chr(((ord($in{$i}) - 0x20) << 2) & 0xFC | ((ord($in{$i + 1}) - 0x20) >> 4) & 0x03));
				$out .= (chr(((ord($in{$i + 1}) - 0x20) << 4) & 0xF0 | ((ord($in{$i + 2}) - 0x20) >> 2) & 0x0F));
				$out .= (chr(((ord($in{$i + 2}) - 0x20) << 6) & 0xC0 | (ord($in{$i + 3}) - 0x20) & 0x3F));
	
				$temp -= 3;
				$i += 4;
			}
			echo substr($out, 0, $len);

			while (ord($in{$i}) != 0x0a) {
				$i++;
			}
			
			$offset = $i + 1;
		}
	}


	function uuencode($in) {
		$out = '';

		for ($i = 0, $j = strlen($in); $i < $j; $i += 3) {
			if (($i % 45) == 0) {
				if (($j - $i) > 45) {
					$out = $out."\n".chr(0x20 + 45);
				} else {
					$out = $out."\n".chr(0x20 + $j - $i);
				}
			}
			
			$out .= chr(0x20 + ((ord($in{$i}) >> 2) & 0x3F));
			$out .= chr(0x20 + (((ord($in{$i}) << 4) | ((ord($in{$i + 1}) >> 4) & 0x0F)) & 0x3F));
			$out .= chr(0x20 + (((ord($in{$i + 1}) << 2) | ((ord($in{$i + 2}) >> 6) & 0x03)) & 0x3F));
			$out .= chr(0x20 + (ord($in{$i + 2}) & 0x3F));
		}
    
		if ($i == $j+1) {
			$out{strlen($out)-1} = '=';
		} elseif ($i == $j+2) {
			$k = strlen($out);
			$out{$k-1} = $out{$k-2} = '=';
		}
		
		// Cut the first \r\n
		return substr($out, 2);
	}
?>
