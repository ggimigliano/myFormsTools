<?php
namespace Gimi\myFormsTools\PckmyMime;

define('MIME_PARSER_START',        1);
define('MIME_PARSER_HEADER',       2);
define('MIME_PARSER_HEADER_VALUE', 3);
define('MIME_PARSER_BODY',         4);
define('MIME_PARSER_BODY_START',   5);
define('MIME_PARSER_BODY_DATA',    6);
define('MIME_PARSER_BODY_DONE',    7);
define('MIME_PARSER_BODY_DONE',    7);
define('MIME_PARSER_END',          8);
 
define('MIME_MESSAGE_START',            1);
define('MIME_MESSAGE_GET_HEADER_NAME',  2);
define('MIME_MESSAGE_GET_HEADER_VALUE', 3);
define('MIME_MESSAGE_GET_BODY',         4);
define('MIME_MESSAGE_GET_BODY_PART',    5);


class myMimeParser{

	public $error='';
	public $error_position = -1;
	public $mbox = 0;
	public $decode_headers = 1;
	public $decode_bodies = 1;
	public $ignore_syntax_errors=1;
	public $warnings=array();

	/* Private variables */
	private $state = MIME_PARSER_START;
	private $buffer = '';
	private $buffer_position = 0;
	private $offset = 0;
	private $parts = array();
	private $part_position = 0;
	private $headers = array();
	private $body_parser;
	private $body_parser_state = MIME_PARSER_BODY_DONE;
	private $body_buffer = '';
	private $body_buffer_position = 0;
	private $body_offset = 0;
	private $current_header = '';
	private $file;
	private $body_file;
	private $position = 0;
	private $body_part_number = 1;
	private $next_token = '';

	/* Private functions */

	 private Function SetError($error)
	{
		$this->error = $error;
		return(0);
	}

	 private Function SetPositionedError($error, $position)
	{
		$this->error_position = $position;
		return($this->SetError($error));
	}

	 private Function SetPositionedWarning($error, $position)
	{
		if(!$this->ignore_syntax_errors)
			return($this->SetPositionedError($error, $position));
		$this->warnings[$position]=$error;
		return(1);
	}

	 private Function SetPHPError($error, &$php_error_message)
	{
		if(IsSet($php_error_message)
		&& strlen($php_error_message))
			$error .= ': '.$php_error_message;
		return($this->SetError($error));
	}

	 private Function Tokenize($string,$separator="")
	{   $found=null;
		if(!strcmp($separator,""))
		{
			$separator=$string;
			$string=$this->next_token;
		}
		for($character=0;$character<strlen($separator);$character++)
		{
			if(GetType($position=strpos($string,$separator[$character]))=='integer')
				$found=(IsSet($found) ? min($found,$position) : $position);
		}
		if(IsSet($found))
		{
			$this->next_token=substr($string,$found+1);
			return(substr($string,0,$found));
		}
		else
		{
			$this->next_token='';
			return($string);
		}
	}

	 private Function ParseStructuredHeader($value, &$type, &$parameters, &$character_sets, &$languages)
	{
		$type = strtolower(trim((string) $this->Tokenize($value, ';')));
		$p = trim((string) $this->Tokenize(''));
		$parameters = $character_sets = $languages = array();
		while(strlen($p))
		{
			$parameter = trim(strtolower($this->Tokenize($p, '=')));
			$value = trim((string) $this->Tokenize(';'));
			if(!strcmp($value[0], '"')
			&& !strcmp($value[strlen($value) - 1], '"'))
				$value = substr($value, 1, strlen($value) - 2);
			$p = trim((string) $this->Tokenize(''));
			if(($l=strlen($parameter))
			&& !strcmp($parameter[$l - 1],'*'))
			{
				$parameter=$this->Tokenize($parameter, '*');
				if(IsSet($parameters[$parameter])
				&& IsSet($character_sets[$parameter]))
					$value = $parameters[$parameter] . UrlDecode($value);
				else
				{
					$character_sets[$parameter] = strtolower($this->Tokenize($value, '\''));
					$languages[$parameter] = $this->Tokenize('\'');
					$value = UrlDecode($this->Tokenize(''));
				}
			}
			$parameters[$parameter] = $value;
		}
	}

	 private Function ParsePart($end, &$part, &$need_more_data)
	{
		$need_more_data = 0;
		switch($this->state)
		{
			case MIME_PARSER_START:
				$part=array(
					'Type'=>'MessageStart',
					'Position'=>$this->offset + $this->buffer_position
				);
				$this->state = MIME_PARSER_HEADER;
				break;
			case MIME_PARSER_HEADER:
				if(GetType($line_break=strpos($this->buffer, $break="\r\n", $this->buffer_position))=='integer'
				|| GetType($line_break=strpos($this->buffer, $break="\n", $this->buffer_position))=='integer'
				|| GetType($line_break=strpos($this->buffer, $break="\r", $this->buffer_position))=='integer')
				{
					$next = $line_break + strlen($break);
					if(!strcmp($break,"\r")
					&& strlen($this->buffer) == $next
					&& !$end)
					{
						$need_more_data = 1;
						break;
					}
					if($line_break==$this->buffer_position)
					{
						$part=array(
							'Type'=>'BodyStart',
							'Position'=>$this->offset + $this->buffer_position
						);
						$this->buffer_position = $next;
						$this->state = MIME_PARSER_BODY;
						break;
					}
				}
				if(GetType($colon=strpos($this->buffer, ':', $this->buffer_position))=='integer')
				{
					if(GetType($space=strpos(substr($this->buffer, $this->buffer_position, $colon - $this->buffer_position), ' '))=='integer')
					{
						if((!$this->mbox
						|| strcmp(strtolower(substr($this->buffer, $this->buffer_position, $space)), 'from'))
						&& !$this->SetPositionedWarning('invalid header name line', $this->buffer_position))
							return(0);
						$next = $this->buffer_position + $space + 1;
					}
					else
						$next = $colon+1;
				}
				else
				{
					$need_more_data = 1;
					break;
				}
				$part=array(
					'Type'=>'HeaderName',
					'Name'=>substr($this->buffer, $this->buffer_position, $next - $this->buffer_position),
					'Position'=>$this->offset + $this->buffer_position
				);
				$this->buffer_position = $next;
				$this->state = MIME_PARSER_HEADER_VALUE;
				break;
			case MIME_PARSER_HEADER_VALUE:
				$position = $this->buffer_position;
				$value = '';
				for(;;)
				{
					if(GetType($line_break=strpos($this->buffer, $break="\r\n", $position))=='integer'
					|| GetType($line_break=strpos($this->buffer, $break="\n", $position))=='integer'
					|| GetType($line_break=strpos($this->buffer, $break="\r", $position))=='integer')
					{
						$next = $line_break + strlen($break);
						$line = substr($this->buffer, $position, $line_break - $position);
						if(strlen($this->buffer) == $next)
						{
							if(!$end)
							{
								$need_more_data = 1;
								break 2;
							}
							$value .= $line;
							$part=array(
								'Type'=>'HeaderValue',
								'Value'=>$value,
								'Position'=>$this->offset + $this->buffer_position
							);
							$this->buffer_position = $next;
							$this->state = MIME_PARSER_END;
							break ;
						}
						else
						{
							$character = $this->buffer[$next];
							if(!strcmp($character, ' ')
							|| !strcmp($character, "\t"))
							{
								$value .= $line;
								$position = $next;
							}
							else
							{
								$value .= $line;
								$part=array(
									'Type'=>'HeaderValue',
									'Value'=>$value,
									'Position'=>$this->offset + $this->buffer_position
								);
								$this->buffer_position = $next;
								$this->state = MIME_PARSER_HEADER;
								break 2;
							}
						}
					}
					else
					{
						if(!$end)
						{
							$need_more_data = 1;
							break;
						}
						else
						{
							$value .= substr($this->buffer, $position);
							$part=array(
								'Type'=>'HeaderValue',
								'Value'=>$value,
								'Position'=>$this->offset + $this->buffer_position
							);
							$this->buffer_position = strlen($this->buffer);
							$this->state = MIME_PARSER_END;
							break;
						}
					}
				}
				break;
			case MIME_PARSER_BODY:
				if($this->mbox)
				{
					$add = 0;
					$append='';
					if(GetType($line_break=strpos($this->buffer, $break="\r\n", $this->buffer_position))=='integer'
					|| GetType($line_break=strpos($this->buffer, $break="\n", $this->buffer_position))=='integer'
					|| GetType($line_break=strpos($this->buffer, $break="\r", $this->buffer_position))=='integer')
					{
						$next = $line_break + strlen($break);
						$following = $next + strlen($break);
						if($following >= strlen($this->buffer)
						|| GetType($line=strpos($this->buffer, $break, $following))!='integer')
						{
							if(!$end)
							{
								$need_more_data = 1;
								break;
							}
						}
						$start = strtolower(substr($this->buffer, $next, strlen($break.'from ')));
						if(!strcmp($break.'from ', $start))
						{
							if($line_break == $this->buffer_position)
							{
								$part=array(
									'Type'=>'MessageEnd',
									'Position'=>$this->offset + $this->buffer_position
								);
								$this->buffer_position = $following;
								$this->state = MIME_PARSER_START;
								break;
							}
							else
								$add = strlen($break);
							$next = $line_break;
						}
						else
						{
							$start = strtolower(substr($this->buffer, $next, strlen('>from ')));
							if(!strcmp('>from ', $start))
							{
								$part=array(
									'Type'=>'BodyData',
									'Data'=>substr($this->buffer, $this->buffer_position, $next - $this->buffer_position),
									'Position'=>$this->offset + $this->buffer_position
								);
								$this->buffer_position = $next + 1;
								break;
							}
						}
					}
					else
					{
						if(!$end)
						{
							$need_more_data = 1;
							break;
						}
						$next = strlen($this->buffer);
						$append="\r\n";
					}
					if($next > $this->buffer_position)
					{
						$part=array(
							'Type'=>'BodyData',
							'Data'=>substr($this->buffer, $this->buffer_position, $next + $add - $this->buffer_position).$append,
							'Position'=>$this->offset + $this->buffer_position
						);
					}
					elseif($end)
					{
						$part=array(
							'Type'=>'MessageEnd',
							'Position'=>$this->offset + $this->buffer_position
						);
						$this->state = MIME_PARSER_END;
					}
					$this->buffer_position = $next;
				}
				else
				{
					if(strlen($this->buffer)-$this->buffer_position)
					{
						$data=substr($this->buffer, $this->buffer_position, strlen($this->buffer) - $this->buffer_position);
						if($end
						&& strcmp(substr($data,-1),"\n")
						&& strcmp(substr($data,-1),"\r"))
							$data.="\n";
						$part=array(
							'Type'=>'BodyData',
							'Data'=>$data,
							'Position'=>$this->offset + $this->buffer_position
						);
						$this->buffer_position = strlen($this->buffer);
						$need_more_data = !$end;
					}
					else
					{
						if($end)
						{
							$part=array(
								'Type'=>'MessageEnd',
								'Position'=>$this->offset + $this->buffer_position
							);
							$this->state = MIME_PARSER_END;
						}
						else
							$need_more_data = 1;
					}
				}
				break;
			default:
				return($this->SetPositionedError($this->state.' is not a valid parser state', $this->buffer_position));
		}
		return(1);
	}

	 private Function QueueBodyParts()
	{$part=$end=null;
		for(;;)
		{
			if(!$this->body_parser->GetPart($part,$end))
				return($this->SetError($this->body_parser->error));
			if($end)
				return(1);
			if(!IsSet($part['Part']))
				$part['Part']=$this->headers['Boundary'];
			$this->parts[]=$part;
		}
	}

	 private Function DecodePart($part)
	{$error_position=null;$error_position;
		switch($part['Type'])
		{
			case 'MessageStart':
				$this->headers=array();
				break;
			case 'HeaderName':
				if($this->decode_bodies)
					$this->current_header = strtolower($part['Name']);
				break;
			case 'HeaderValue':
				if($this->decode_headers)
				{
					$value = $part['Value'];
					$error = '';
					for($decoded_header = array(), $position = 0; $position<strlen($value); )
					{
						if(GetType($encoded=strpos($value,'=?', $position))!='integer')
						{
							if($position<strlen($value))
							{
								if(count($decoded_header))
									$decoded_header[count($decoded_header)-1]['Value'].=substr($value, $position);
								else
								{
									$decoded_header[]=array(
										'Value'=>substr($value, $position),
										'Encoding'=>'ASCII'
									);
								}
							}
							break;
						}
						$set = $encoded + 2;
						if(GetType($method=strpos($value,'?', $set))!='integer')
						{
							$error = 'invalid header encoding syntax '.$part['Value'];
							$error_position = $part['Position'] + $set;
							break;
						}
						$encoding=strtoupper(substr($value, $set, $method - $set));
						$method += 1;
						if(GetType($data=strpos($value,'?', $method))!='integer')
						{
							$error = 'invalid header encoding syntax '.$part['Value'];
							$error_position = $part['Position'] + $set;
							break;
						}
						$start = $data + 1;
						if(GetType($end=strpos($value,'?=', $start))!='integer')
						{
							$error = 'invalid header encoding syntax '.$part['Value'];
							$error_position = $part['Position'] + $start;
							break;
						}
						if($encoded > $position)
						{
							if(count($decoded_header))
								$decoded_header[count($decoded_header)-1]['Value'].=substr($value, $position, $encoded - $position);
							else
							{
								$decoded_header[]=array(
									'Value'=>substr($value, $position, $encoded - $position),
									'Encoding'=>'ASCII'
								);
							}
						}
						switch(strtolower(substr($value, $method, $data - $method)))
						{
							case 'q':
								if($end>$start)
								{
									for($decoded = '', $position = $start; $position < $end ; )
									{
										switch($value[$position])
										{
											case '=':
												$h = HexDec($hex = strtolower(substr($value, $position+1, 2)));
												if($end - $position < 3
												|| strcmp(sprintf('%02x', $h), $hex))
												{
													$warning = 'the header specified an invalid encoded character';
													$warning_position = $part['Position'] + $position + 1;
													if($this->ignore_syntax_errors)
													{
														$this->SetPositionedWarning($warning, $warning_position);
														$decoded .= '=';
														$position ++;
													}
													else
													{
														$error = $warning;
														$error_position = $warning_position;
														break 4;
													}
												}
												else
												{
													$decoded .= Chr($h);
													$position += 3;
												}
												break;
											case '_':
												$decoded .= ' ';
												$position++;
												break;
											default:
												$decoded .= $value[$position];
												$position++;
												break;
										}
									}
									if(count($decoded_header)
									&& (!strcmp($decoded_header[$last = count($decoded_header)-1]['Encoding'], 'ASCII'))
									|| !strcmp($decoded_header[$last]['Encoding'], $encoding))
									{
										$decoded_header[$last]['Value'].= $decoded;
										$decoded_header[$last]['Encoding']= $encoding;
									}
									else
									{
										$decoded_header[]=array(
											'Value'=>$decoded,
											'Encoding'=>$encoding
										);
									}
								}
								break;
							case 'b':
								if($end>$start)
								{
									$decoded=base64_decode(substr($value, $start, $end - $start));
									if(count($decoded_header)
									&& (!strcmp($decoded_header[$last = count($decoded_header)-1]['Encoding'], 'ASCII'))
									|| !strcmp($decoded_header[$last]['Encoding'], $encoding))
									{
										$decoded_header[$last]['Value'].= $decoded;
										$decoded_header[$last]['Encoding']= $encoding;
									}
									else
									{
										$decoded_header[]=array(
											'Value'=>$decoded,
											'Encoding'=>$encoding
										);
									}
								}
								break;
							default:
								$error = 'the header specified an unsupported encoding method';
								$error_position = $part['Position'] + $method;
								break 2;
						}
						$position = $end + 2;
					}
					if(strlen($error)==0
					&& count($decoded_header))
						$part['Decoded']=$decoded_header;
				}
				if($this->decode_bodies
				|| $this->decode_headers)
				{
					switch($this->current_header)
					{
						case 'content-type:':
							$value = $part['Value'];
							$type = strtolower(trim(strtok($value, ';')));
							$parameters = trim(strtok(''));
							$this->headers['Type'] = $type;
							if($this->decode_headers)
							{
								$part['MainValue'] = $type;
								$part['Parameters'] = array();
							}
							if(!strcmp(strtok($type, '/'), 'multipart'))
							{
								$this->headers['Multipart'] = 1;
								while(strlen($parameters))
								{
									$parameter = strtolower(strtok($parameters, '='));
									$value = trim(strtok(';'));
									if(!strcmp($value[0], '"')
									&& !strcmp($value[strlen($value) - 1], '"'))
										$value = substr($value, 1, strlen($value) - 2);
									if($this->decode_headers)
										$part['Parameters'][$parameter] = $value;
									if(!strcmp($parameter, 'boundary'))
										$this->headers['Boundary'] = $value;
									$parameters = trim(strtok(''));
								}
								if(!IsSet($this->headers['Boundary']))
									return($this->SetPositionedError('multipart content-type header does not specify the boundary parameter', $part['Position']));
							}
							break;
						case 'content-transfer-encoding:':
							switch($this->headers['Encoding']=strtolower(trim((string) $part['Value'])))
							{
								case 'quoted-printable':
									$this->headers['QuotedPrintable'] = 1;
									break;
								case '7 bit':
								case '8 bit':
									if(!$this->SetPositionedWarning('"'.$this->headers['Encoding'].'" is an incorrect content transfer encoding type', $part['Position']))
										return(0);
								case '7bit':
								case '8bit':
								case 'binary':
									break;
								case 'base64':
									$this->headers['Base64']=1;
									break;
								default:
									if(!$this->SetPositionedWarning('decoding '.$this->headers['Encoding'].' encoded bodies is not yet supported', $part['Position']))
										return(0);
							}
							break;
					}
				}
				break;
			case 'BodyStart':
				if($this->decode_bodies
				&& IsSet($this->headers['Multipart']))
				{
					$this->body_parser_state = MIME_PARSER_BODY_START;
					$this->body_buffer = '';
					$this->body_buffer_position = 0;
				}
				break;
			case 'MessageEnd':
				if($this->decode_bodies
				&& IsSet($this->headers['Multipart'])
				&& $this->body_parser_state != MIME_PARSER_BODY_DONE)
					return($this->SetPositionedError('incomplete message body part', $part['Position']));
				break;
			case 'BodyData':
				if($this->decode_bodies)
				{
					if(strlen($this->body_buffer)==0)
					{
						$this->body_buffer = $part['Data'];
						$this->body_offset = $part['Position'];
					}
					else
						$this->body_buffer .= $part['Data'];
					if(IsSet($this->headers['Multipart']))
					{
						$boundary = '--'.$this->headers['Boundary'];
						switch($this->body_parser_state)
						{
							case MIME_PARSER_BODY_START:
								for($position = $this->body_buffer_position; ;)
								{
									if(GetType($line_break=strpos($this->body_buffer, $break="\r\n", $position))!='integer'
									&& GetType($line_break=strpos($this->body_buffer, $break="\n", $position))!='integer'
									&& GetType($line_break=strpos($this->body_buffer, $break="\r", $position))!='integer')
										return(1);
									$next = $line_break + strlen($break);
									if(!strcmp(substr($this->body_buffer, $position, $line_break - $position), $boundary))
									{
										$part=array(
											'Type'=>'StartPart',
											'Part'=>$this->headers['Boundary'],
											'Position'=>$this->body_offset + $next
										);
										$this->parts[]=$part;
										UnSet($this->body_parser);
										$this->body_parser = new myMimeParser();
										$this->body_parser->decode_bodies = 1;
										$this->body_parser->decode_headers = $this->decode_headers;
										$this->body_parser->mbox = 0;
										$this->body_parser_state = MIME_PARSER_BODY_DATA;
										$this->body_buffer = substr($this->body_buffer, $next);
										$this->body_offset += $next;
										$this->body_buffer_position = 0;
										break;
									}
									else
										$position = $next;
								}
							case MIME_PARSER_BODY_DATA:
								for($position = $this->body_buffer_position; ;)
								{
									if(GetType($line_break=strpos($this->body_buffer, $break="\r\n", $position))!='integer'
									&& GetType($line_break=strpos($this->body_buffer, $break="\n", $position))!='integer'
									&& GetType($line_break=strpos($this->body_buffer, $break="\r", $position))!='integer')
									{
										if($position > 0)
										{
											if(!$this->body_parser->Parse(substr($this->body_buffer, 0, $position), 0))
												return($this->SetError($this->body_parser->error));
											if(!$this->QueueBodyParts())
												return(0);
										}
										$this->body_buffer = substr($this->body_buffer, $position);
										$this->body_buffer_position = 0;
										$this->body_offset += $position;
										return(1);
									}
									$next = $line_break + strlen($break);
									$line = substr($this->body_buffer, $position, $line_break - $position);
									if(!strcmp($line, $boundary))
									{
										if(!$this->body_parser->Parse(substr($this->body_buffer, 0, $position), 1))
											return($this->SetError($this->body_parser->error));
										if(!$this->QueueBodyParts())
											return(0);
										$part=array(
											'Type'=>'EndPart',
											'Part'=>$this->headers['Boundary'],
											'Position'=>$this->body_offset + $position
										);
										$this->parts[] = $part;
										$part=array(
											'Type'=>'StartPart',
											'Part'=>$this->headers['Boundary'],
											'Position'=>$this->body_offset + $next
										);
										$this->parts[] = $part;
										UnSet($this->body_parser);
										$this->body_parser = new myMimeParser();
										$this->body_parser->decode_bodies = 1;
										$this->body_parser->decode_headers = $this->decode_headers;
										$this->body_parser->mbox = 0;
										$this->body_buffer = substr($this->body_buffer, $next);
										$this->body_buffer_position = 0;
										$this->body_offset += $next;
										$position=0;
										continue;
									}
									elseif(!strcmp($line, $boundary.'--'))
									{
										if(!$this->body_parser->Parse(substr($this->body_buffer, 0, $position), 1))
											return($this->SetError($this->body_parser->error));
										if(!$this->QueueBodyParts())
											return(0);
										$part=array(
											'Type'=>'EndPart',
											'Part'=>$this->headers['Boundary'],
											'Position'=>$this->body_offset + $position
										);
										$this->body_buffer = substr($this->body_buffer, $next);
										$this->body_buffer_position = 0;
										$this->body_offset += $next;
										$this->body_parser_state = MIME_PARSER_BODY_DONE;
										break 2;
									}
									$position = $next;
								}
								break;
							case MIME_PARSER_BODY_DONE:
								return(1);
							default:
								return($this->SetPositionedError($this->state.' is not a valid body parser state', $this->body_buffer_position));
						}
					}
					elseif(IsSet($this->headers['QuotedPrintable']))
					{
						for($end = strlen($this->body_buffer), $decoded = '', $position = $this->body_buffer_position; $position < $end; )
						{
							if(GetType($equal = strpos($this->body_buffer, '=', $position))!='integer')
							{
								$decoded .= substr($this->body_buffer, $position);
								$position = $end;
								break;
							}
							$next = $equal + 1;
							switch($end - $equal)
							{
								case 1:
									$decoded .= substr($this->body_buffer, $position, $equal - $position);
									$position = $equal;
									break 2;
								case 2:
									$decoded .= substr($this->body_buffer, $position, $equal - $position);
									if(!strcmp($this->body_buffer[$next],"\n"))
										$position = $end;
									else
										$position = $equal;
									break 2;
							}
							if(!strcmp(substr($this->body_buffer, $next, 2), $break="\r\n")
							|| !strcmp($this->body_buffer[$next], $break="\n")
							|| !strcmp($this->body_buffer[$next], $break="\r"))
							{
								$decoded .= substr($this->body_buffer, $position, $equal - $position);
								$position = $next + strlen($break);
								continue;
							}
							$decoded .= substr($this->body_buffer, $position, $equal - $position);
							$h = HexDec($hex=strtolower(substr($this->body_buffer, $next, 2)));
							if(strcmp(sprintf('%02x', $h), $hex))
							{
								if(!$this->SetPositionedWarning('the body specified an invalid quoted-printable encoded character', $this->body_offset + $next))
									return(0);
								$decoded.='=';
								$position=$next;
							}
							else
							{
								$decoded .= Chr($h);
								$position = $equal + 3;
							}
						}
						if(strlen($decoded)==0)
						{
							$this->body_buffer_position = $position;
							return(1);
						}
						$part['Data'] = $decoded;
						$this->body_buffer = substr($this->body_buffer, $position);
						$this->body_buffer_position = 0;
						$this->body_offset += $position;
					}
					elseif(IsSet($this->headers['Base64']))
					{
						$part['Data'] = base64_decode($this->body_buffer_position ? substr($this->body_buffer,$this->body_buffer_position) : $this->body_buffer);
						$this->body_offset += strlen($this->body_buffer) - $this->body_buffer_position;
						$this->body_buffer_position = 0;
						$this->body_buffer = '';
					}
					else
					{
						$part['Data'] = substr($this->body_buffer, $this->body_buffer_position);
						$this->body_buffer_position = 0;
						$this->body_buffer = '';
					}
				}
				break;
		}
		$this->parts[]=$part;
		return(1);
	}

	 private Function DecodeStream($parameters, &$end_of_message, &$decoded)
	{  $part=$languages= $end_of_part= $decoded_part=$character_sets=$header_parameters=$end=null;$php_errormsg=null;;
		$end_of_message = 1;
		$state = MIME_MESSAGE_START;
		for(;;)
		{
			if(!$this->GetPart($part, $end))
				return(0);
			if($end)
			{
				if(IsSet($parameters['File']))
				{
					$end_of_data = feof($this->file);
					if($end_of_data)
						break;
					$data = @fread($this->file, 8000);
					if(GetType($data)!='string')
						return($this->SetPHPError('could not read the message file', $php_errormsg));
					$end_of_data = feof($this->file);
				}
				else
				{
					$end_of_data=($this->position>=strlen($parameters['Data']));
					if($end_of_data)
						break;
					$data = substr($parameters['Data'], $this->position);
					$end_of_data = 1;
					$this->position = strlen($parameters['Data']);
				}
				if(!$this->Parse($data, $end_of_data))
					return(0);
				continue;
			}
			$type = $part['Type'];
			switch($state)
			{
				case MIME_MESSAGE_START:
					switch($type)
					{
						case 'MessageStart':
							$decoded=array(
								'Headers'=>array(),
								'Parts'=>array()
							);
							$end_of_message = 0;
							$state = MIME_MESSAGE_GET_HEADER_NAME;
							continue 3;
					}
					break;

				case MIME_MESSAGE_GET_HEADER_NAME:
					switch($type)
					{
						case 'HeaderName':
							$header = strtolower($part['Name']);
							$state = MIME_MESSAGE_GET_HEADER_VALUE;
							continue 3;
						case 'BodyStart':
							$state = MIME_MESSAGE_GET_BODY;
							$part_number = 0;
							continue 3;
					}
					break;

				case MIME_MESSAGE_GET_HEADER_VALUE:
					switch($type)
					{
						case 'HeaderValue':
							$value = trim((string) $part['Value']);
							if(!IsSet($decoded['Headers'][$header]))
							{
								$h = 0;
								$decoded['Headers'][$header]=$value;
							}
							elseif(GetType($decoded['Headers'][$header])=='string')
							{
								$h = 1;
								$decoded['Headers'][$header]=array($decoded['Headers'][$header], $value);
							}
							else
							{
								$h = count($decoded['Headers'][$header]);
								$decoded['Headers'][$header][]=$value;
							}
							if(IsSet($part['Decoded'])
							&& (count($part['Decoded'])>1
							|| strcmp($part['Decoded'][0]['Encoding'],'ASCII')
							|| strcmp($value, trim((string) $part['Decoded'][0]['Value']))))
							{
								$p=$part['Decoded'];
								$p[0]['Value']=ltrim((string) $p[0]['Value']);
								$last=count($p)-1;
								$p[$last]['Value']=rtrim((string) $p[$last]['Value']);
								$decoded['DecodedHeaders'][$header][$h]=$p;
							}
							switch($header)
							{
								case 'content-disposition:':
									$filename='filename';
									break;
								case 'content-type:':
									if(!IsSet($decoded['FileName']))
									{
										$filename='name';
										break;
									}
								default:
									$filename='';
									break;
							}
							if(strlen($filename))
							{
								$this->ParseStructuredHeader($value, $type, $header_parameters, $character_sets, $languages);
								if(IsSet($header_parameters[$filename]))
								{
									$decoded['FileName']=$header_parameters[$filename];
									if(IsSet($character_sets[$filename])
									&& strlen($character_sets[$filename]))
										$decoded['FileNameCharacterSet']=$character_sets[$filename];
									if(IsSet($character_sets['language'])
									&& strlen($character_sets['language']))
										$decoded['FileNameCharacterSet']=$character_sets[$filename];
									if(!strcmp($header, 'content-disposition:'))
										$decoded['FileDisposition']=$type;
								}
							}
							$state = MIME_MESSAGE_GET_HEADER_NAME;
							continue 3;
					}
					break;

				case MIME_MESSAGE_GET_BODY:
					switch($type)
					{
						case 'BodyData':
							if(IsSet($parameters['SaveBody']))
							{
								if(!IsSet($decoded['BodyFile']))
								{
									$directory_separator=(defined('DIRECTORY_SEPARATOR') ? DIRECTORY_SEPARATOR : '/');
									$path = (strlen($parameters['SaveBody']) ? ($parameters['SaveBody'].(strcmp($parameters['SaveBody'][strlen($parameters['SaveBody'])-1], $directory_separator) ? $directory_separator : '')) : '').strval($this->body_part_number);
									if(!($this->body_file = fopen($path, 'wb')))
										return($this->SetPHPError('could not create file '.$path.' to save the message body part', $php_errormsg));
									$decoded['BodyFile'] = $path;
									$decoded['BodyPart'] = $this->body_part_number;
									$decoded['BodyLength'] = 0;
									$this->body_part_number++;
								}
								if(strlen($part['Data'])
								&& !fwrite($this->body_file, $part['Data']))
								{
									$this->SetPHPError('could not save the message body part to file '.$decoded['BodyFile'], $php_errormsg);
									fclose($this->body_file);
									@unlink($decoded['BodyFile']);
									return(0);
								}
							}
							elseif(IsSet($parameters['SkipBody']))
							{
								if(!IsSet($decoded['BodyPart']))
								{
									$decoded['BodyPart'] = $this->body_part_number;
									$decoded['BodyLength'] = 0;
									$this->body_part_number++;
								}
							}
							else
							{
								if(IsSet($decoded['Body']))
									$decoded['Body'].=$part['Data'];
								else
								{
									$decoded['Body']=$part['Data'];
									$decoded['BodyPart'] = $this->body_part_number;
									$decoded['BodyLength'] = 0;
									$this->body_part_number++;
								}
							}
							$decoded['BodyLength'] += strlen($part['Data']);
							continue 3;
						case 'StartPart':
							if(!$this->DecodeStream($parameters, $end_of_part, $decoded_part))
								return(0);
							$decoded['Parts'][$part_number]=$decoded_part;
							$part_number++;
							$state = MIME_MESSAGE_GET_BODY_PART;
							continue 3;
						case 'MessageEnd':
							if(IsSet($decoded['BodyFile']))
								fclose($this->body_file);
							return(1);
					}
					break;

				case MIME_MESSAGE_GET_BODY_PART:
					switch($type)
					{
						case 'EndPart':
							$state = MIME_MESSAGE_GET_BODY;
							continue 3;
					}
					break;
			}
			return($this->SetError('unexpected decoded message part type '.$type.' in state '.$state));
		}
		return(1);
	}


	/* Public functions */

	 public Function Parse($data, $end)
	   {$part= $need_more_data=null;
		if(strlen($this->error))
			return(0);
		if($this->state==MIME_PARSER_END)
			return($this->SetError('the parser already reached the end'));
		$this->buffer .= $data;
		do
		{
			Unset($part);
			if(!$this->ParsePart($end, $part, $need_more_data))
				return(0);
			if(IsSet($part)
			&& !$this->DecodePart($part))
				return(0);
		}
		while(!$need_more_data
		&& $this->state!=MIME_PARSER_END);
		if($end
		&& $this->state!=MIME_PARSER_END)
			return($this->SetError('reached a premature end of data'));
		if($this->buffer_position>0)
		{
			$this->offset += $this->buffer_position;
			$this->buffer = substr($this->buffer, $this->buffer_position);
			$this->buffer_position = 0;
		}
		return(1);
	}

	
	 public Function GetPart(&$part, &$end)
	{
		$end = ($this->part_position >= count($this->parts));
		if($end)
		{
			if($this->part_position)
			{
				$this->part_position = 0;
				$this->parts = array();
			}
		}
		else
		{
			$part = $this->parts[$this->part_position];
			$this->part_position ++;
		}
		return(1);
	}


	 public Function Decode($parameters, &$decoded)
	{ $end_of_message= $decoded_message=$php_errormsg=null;
		if(IsSet($parameters['File']))
		{
			if(!($this->file = @fopen($parameters['File'], 'r')))
				return($this->SetPHPError('could not open the message file to decode '.$parameters['File'], $php_errormsg));
		}
		elseif(IsSet($parameters['Data']))
			$this->position = 0;
		else
			return($this->SetError('it was not specified a valid message to decode'));
		$this->warnings = $decoded = array();
		for($message = 0; ($success = $this->DecodeStream($parameters, $end_of_message, $decoded_message)) && !$end_of_message; $message++)
			$decoded[$message]=$decoded_message;
		if(IsSet($parameters['File']))
			fclose($this->file);
		return($success);
	}


	public function parseString($string,$cartella,$clean=true) {
		return $this->myparse('',$string,$cartella,$clean);
	}
	
	public function ParseFile($file,$cartella,$clean=true) {
		return $this->myparse($file,'',$cartella,$clean);
	}
	
	
	
	
	/**
	 * @ignore
	 */
	private function myparse($file,$string,$cartella,$clean=true){
		/*
		 * Set to 0 for parsing a single message file
		 * Set to 1 for parsing multiple messages in a single file in the mbox format
		 */
		$this->mbox = 1;
		/*
		 * Set to 0 for not decoding the message bodies
		 */
		$this->decode_bodies = 1;
		
		/*
		 * Set to 0 to make syntax errors make the decoding fail
		 */
		$decoded=null;
		$this->ignore_syntax_errors = 1;
		$array=array("SkipBody"=>1,'SaveBody'=>$cartella);
		if($file) $array['File']=$file;
				elseif($string) $array['Data']=$string;
		$esito=$this->Decode($array,$decoded);
	
		if (!$esito) return $esito;
		$parti=$this->rinominaAttach($decoded,$cartella,$clean);
		$parti['Headers']=$decoded[0]['Headers'];
		return $parti;
	}
	
	private function rinominaAttach($parti,$cartella,$clean=true) {
		$out=array();
		if (!count($parti)) $out;
		foreach ($parti as $parte) {
			$out+=$this->rinominaAttach($parte['Parts'],$cartella,$clean);
			if ($parte['BodyLength']>0){
				if ($parte['FileName'] && $parte['BodyFile']) @rename($parte['BodyFile'],"$cartella/$parte[FileName]");
								else $out['Contents'][]=@file_get_contents($parte['BodyFile']);
						}
			if ($clean) @unlink($parte['BodyFile']);
			if ($parte['FileName']) $out['Files'][]=$parte['FileName'];
		}
		return $out;
	}
	

};


?>