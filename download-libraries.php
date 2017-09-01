<?php


$page_url   = 'http://www.ftdichip.com/Drivers/D2XX.htm';
$url_prefix = 'http://www.ftdichip.com/Drivers/';


$cwd = \getcwd();
$html = \file_get_contents($page_url);
if ($html === FALSE) {
	echo "Failed to download url: $page_url\n";
	exit(1);
}
$html = \str_replace(
	[ "\r", "\t" ],
	[ "\n", ' '  ],
	$html
);


$funcTrimLeft = function($text) {
	if (empty($text)) return $text;
	while (TRUE) {
		$first = \substr($text, 0, 1);
		switch ($first) {
		case ' ':
		case '"':
		case "'":
		case "\t":
		case "\r":
		case "\n":
			$text = \substr($text, 1);
			continue;
		default:
			return $text;
		}
	}
};
$funcTrimRight = function($text) {
	if (empty($text)) return $text;
	while (TRUE) {
		$last = \substr($text, -1, 1);
		switch ($last) {
		case ' ':
		case '"':
		case "'":
		case "\t":
		case "\r":
		case "\n":
			$text = \substr($text, 0, -1);
			continue;
		default:
			return $text;
		}
	}
};
$funcTrim = function($text) {
	global $funcTrimLeft, $funcTrimRight;
	$text = $funcTrimLeft( $text);
	$text = $funcTrimRight($text);
	return $text;
};


$funcCheckExplodeArray = function(&$array, $line, $expected=2) {
	if ($array === NULL) {
		echo "\n ** Failed on line $line ** \n\n";
		exit(1);
	}
	if ($expected !== NULL) {
		if (\count($array) != $expected) {
			echo "\n ** Failed to parse on line $line expected $expected ** \n\n";
			exit(1);
		}
	}
};


$funcParseURL = function(&$html) {
	global $funcCheckExplodeArray, $funcTrim;
	global $url_prefix;
	$array = \explode('<a ',   $html,     2); $funcCheckExplodeArray($array, __LINE__);
	$array = \explode('href=', $array[1], 2); $funcCheckExplodeArray($array, __LINE__);
	$array = \explode('>',     $array[1], 2); $funcCheckExplodeArray($array, __LINE__);
	$result = $funcTrim($array[0]);
	$html = $array[1];
	if (\strpos($result, ' ') !== FALSE) {
		$array = \explode(' ', $result);
		$result = $funcTrim($array[0]);
	}
	unset($array);
	return $url_prefix.$result;
};
$funcParseVersion = function(&$html) {
	global $funcCheckExplodeArray, $funcTrim;
	$array = \explode('<', $html, 2); $funcCheckExplodeArray($array, __LINE__);
	$result = $array[0];
	$html = $array[1];
	if (\strpos($result, ' ') !== FALSE) {
		$array = \explode(' ', $result, 2); $funcCheckExplodeArray($array, __LINE__);
		$result = $array[0];
	}
	unset($array);
	return $funcTrim($result);
};


$info = [];


// windows
$array = \explode('>Windows*</td>', $html, 2); $funcCheckExplodeArray($array, __LINE__);
$html = $array[1]; unset($array);
// win32
$info['win32'] = [
	'url'     => $funcParseURL(    $html),
	'version' => $funcParseVersion($html)
];
// win64
$info['win64'] = [
	'url'     => $funcParseURL(    $html),
	'version' => $funcParseVersion($html)
];

// linux
$array = \explode('>Linux</td>', $html, 2); $funcCheckExplodeArray($array, __LINE__);
$html = $array[1]; unset($array);
// linux 32
$info['linux32'] = [
	'url'     => $funcParseURL(    $html),
	'version' => $funcParseVersion($html)
];
// linux 64
$info['linux64'] = [
	'url'     => $funcParseURL(    $html),
	'version' => $funcParseVersion($html)
];

// arm
$info['arm5-soft-float'] = [
	'url'     => $funcParseURL(    $html),
	'version' => $funcParseVersion($html)
];
$info['arm5-soft-float-uclibc'] = [
	'url'     => $funcParseURL(    $html),
	'version' => $funcParseVersion($html)
];
$info['arm6-hard-float'] = [
	'url'     => $funcParseURL(    $html),
	'version' => $funcParseVersion($html)
];
$info['arm7-hard-float'] = [
	'url'     => $funcParseURL(    $html),
	'version' => $funcParseVersion($html)
];
$info['arm8-hard-float'] = [
	'url'     => $funcParseURL(    $html),
	'version' => $funcParseVersion($html)
];

// mac
$array = \explode('>Mac OS X<br>', $html, 2); $funcCheckExplodeArray($array, __LINE__);
$html = $array[1]; unset($array);
$info['mac'] = [
	'url'     => $funcParseURL(    $html),
	'version' => $funcParseVersion($html)
];

// android
$array = \explode('>Android (Java D2XX)</td>', $html, 2); $funcCheckExplodeArray($array, __LINE__);
$html = $array[1]; unset($array);
$info['android'] = [
	'url'     => $funcParseURL($html),
	'version' => ''
];
$array = \explode('<br>',  $html,     2); $funcCheckExplodeArray($array, __LINE__);
$array = \explode('</td>', $array[1], 2); $funcCheckExplodeArray($array, __LINE__);
$info['android']['version'] = $funcTrim( $array[0] );
$html = $array[1]; unset($array);


// download files
foreach ($info as $v1 => $v2) {
	$version = $v2['version'];
	$url     = $v2['url'];
	$path = "$cwd/libraries";
	$ext = '';
	$pos = \strrpos($url, '.');
	if ($pos !== FALSE) {
		$ext = \substr($url, $pos);
	}
	$filePath = "{$path}/{$v1}-{$version}{$ext}";
	echo "\n";
	echo "Downloading $v1..\n";
	echo "Version: $version\n";
	echo "    URL: $url\n";
	echo "To File: $filePath\n";
	$handleIn = \fopen($url, 'rb');
	if ($handleIn === FALSE) {
		echo "\n ** Failed to download file on line ".__LINE__." url: $url ** \n\n";
		exit(1);
	}
	$handleOut = \fopen($filePath, 'wb');
	if ($handleOut === FALSE) {
		echo "\n ** Failed to write downloaded file on line ".__LINE__." path: $filePath ** \n\n";
		exit(1);
	}
	$dotSize = 1024 * 102.4; // 100KB
	$chunkSize = 1024 * 8;   // 8KB
	$downloadedSize = 0;
	$lastDotSize    = 0;
	while(!\feof($handleIn)) {
		\fwrite(
			$handleOut,
			\fread(
				$handleIn,
				$chunkSize
			),
			$chunkSize
		);
		$downloadedSize += $chunkSize;
		if ($downloadedSize - $lastDotSize > $dotSize) {
			echo ' .';
			$lastDotSize = $downloadedSize;
		}
	}
	echo "\n";
	$fileSize = \filesize($filePath);
	$sizeStr = '';
	if ($fileSize > 1024 * 1024 * 1024) {
		$size = \round( $fileSize / (1024.0 * 1024.0 * 102.4) ) / 10.0;
		$sizeStr = "{$size}GB";
	} else
	if ($fileSize > 1024 * 1024) {
		$size = \round( $fileSize / (1024.0 * 102.4) ) / 10.0;
		$sizeStr = "{$size}MB";
	} else
	if ($fileSize > 1024) {
		$size = \round( $fileSize / 102.4 ) / 10.0;
		$sizeStr = "{$size}KB";
	} else {
		$sizeStr = "{$fileSize}B";
	}
	echo "Downloaded $sizeStr\n";
	echo "\n";
}
echo "\n\n";

