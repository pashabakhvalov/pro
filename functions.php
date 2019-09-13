<?php
function conv($n) 
{
	if (is_array($n))
	{
		return array_map("conv", $n);
	}
	else
	{
		return iconv('windows-1251', 'utf-8', $n);
	}
}
?>