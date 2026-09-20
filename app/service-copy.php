<?php
declare(strict_types=1);
/** Editorial replacements respect the role of each slot: short heading + body. */
function rewrite_service_copy(Dom\HTMLDocument $doc,Dom\Element $root,string $route):void {
 $copy=editorial_services()[ltrim($route,'/')]??null;if(!$copy)return;
 $h1=$root->querySelector('h1');if($h1){$title=$h1->childElementCount===0?$h1:$h1->querySelector('span');if($title)$title->textContent=$copy['title'];$hero=$h1;while($hero->parentElement&&$hero->parentElement!==$root)$hero=$hero->parentElement;$lead=$hero->querySelector('.whitespace-pre-line');if($lead)$lead->textContent=$copy['lead'];}
 $find=function(string $pattern)use($root):?Dom\Element{foreach($root->querySelectorAll('h2,h3,div')as $n){if($n->childElementCount===0&&preg_match($pattern,trim($n->textContent)))return $n;}return null;};
 $top=function(Dom\Element $n)use($root):Dom\Element{while($n->parentElement&&$n->parentElement!==$root)$n=$n->parentElement;return $n;};
 $indications=$find('/^(когда|кому|показания|в каких случаях)/iu');
 if($indications){$block=$top($indications);$slots=[];foreach($block->querySelectorAll('.uppercase')as$n){if($n->childElementCount===0&&!preg_match('/запис|консультац/iu',$n->textContent))$slots[]=$n;}
 foreach($slots as$i=>$slot){$item=$copy['indications'][$i]??null;if(!$item)continue;$slot->textContent=$item['title'];$slot->setAttribute('data-feature-title','');$slot->setAttribute('class',str_replace('uppercase','',$slot->getAttribute('class')));$parent=$slot->parentElement;$parent->setAttribute('data-feature-copy','');$p=$doc->createElement('p');$p->setAttribute('class','dz-feature-description');$p->textContent=$item['text'];$slot->after($p);$card=$parent;while($card->parentElement&&$card!==$block&&!str_contains($card->getAttribute('class')??'','rounded'))$card=$card->parentElement;if($card!==$block){$card->setAttribute('data-feature-card','');$card->parentElement->setAttribute('data-feature-grid','');$card->parentElement->setAttribute('data-feature-columns',count($slots)===3?'3':'2');}}
 }
 $steps=$find('/^(этапы|как проходит лечение|ход процедуры)/iu');
 if($steps){$block=$top($steps);$block->setAttribute('data-treatment-steps','');$block->setAttribute('class',str_replace('dz-light','dz-dark',$block->getAttribute('class')));$heads=[];foreach($block->querySelectorAll('h3,.uppercase')as$n){if($n->childElementCount===0&&!preg_match('/запис|консультац/iu',$n->textContent))$heads[]=$n;}
 foreach($heads as$i=>$heading){$item=$copy['steps'][$i]??null;if(!$item)continue;$heading->textContent=$item['title'];$heading->setAttribute('data-step-title','');$card=$heading->parentElement;$card->setAttribute('data-step-card','');$body=$heading->nextElementSibling;if($body){$leaf=$body->querySelector('p,li>span,span')??$body;if($leaf->childElementCount===0)$leaf->textContent=$item['text'];}}
 }
 // Narrative and FAQ blocks keep their original complete meaning and geometry.
 // Do not assign paragraphs into uppercase labels or inferred alternating leaves.
}
