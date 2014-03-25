/**
 * $Id$
 *
 * Cherokee char processor
 *
 * This software is protected by patent No.2009611147 issued on 20.02.2009 by Russian Federal Service for Intellectual Property Patents and Trademarks.
 *
 * @author Konstantin Wiolowan
 * @copyright 2008-2009 Konstantin Wiolowan <wiolowan@mail.ru>
 * @version $Rev$
 * @lastchange $Author$ $Date$
 */
new function(){var i= /[^adeghik-oqs-wyz]/,I={a:'Ꭰ',e:'Ꭱ',i:'Ꭲ',o:'Ꭳ',u:'Ꭴ',v:'Ꭵ',ga:'Ꭶ',ka:'Ꭷ',ge:'Ꭸ',gi:'Ꭹ',go:'Ꭺ',ge:'Ꭻ',gv:'Ꭼ',ke:'Ꭸ',ki:'Ꭹ',ko:'Ꭺ',ke:'Ꭻ',kv:'Ꭼ',ha:'Ꭽ',he:'Ꭾ',hi:'Ꭿ',ho:'Ꮀ',hu:'Ꮁ',hv:'Ꮂ',la:'Ꮃ',le:'Ꮄ',li:'Ꮅ',lo:'Ꮆ',lu:'Ꮇ',lv:'Ꮈ',ma:'Ꮉ',me:'Ꮊ',mi:'Ꮋ',mo:'Ꮌ',mu:'Ꮍ',hna:'Ꮏ',na:'Ꮎ',ne:'Ꮑ',ni:'Ꮒ',no:'Ꮓ',nu:'Ꮔ',nv:'Ꮕ',qua:'Ꮖ',que:'Ꮗ',qui:'Ꮘ',quo:'Ꮙ',quu:'Ꮚ',quv:'Ꮛ',kwa:'Ꮖ',kwe:'Ꮗ',kwi:'Ꮘ',kwo:'Ꮙ',kwu:'Ꮚ',kwv:'Ꮛ',gwa:'Ꮖ',gwe:'Ꮗ',gwi:'Ꮘ',gwo:'Ꮙ',gwu:'Ꮚ',gwv:'Ꮛ','Ꮝa':'Ꮜ','Ꮝe':'Ꮞ','Ꮝi':'Ꮟ','Ꮝo':'Ꮠ','Ꮝu':'Ꮡ','Ꮝv':'Ꮢ',da:'Ꮣ',ta:'Ꮤ',de:'Ꮥ',te:'Ꮦ',di:'Ꮧ',ti:'Ꮨ','do':'Ꮩ',du:'Ꮪ',dv:'Ꮫ',to:'Ꮩ',tu:'Ꮪ',tv:'Ꮫ',dla:'Ꮬ',tla:'Ꮭ',tle:'Ꮮ',tli:'Ꮯ',tlo:'Ꮰ',tlu:'Ꮱ',tlv:'Ꮲ',dle:'Ꮮ',dli:'Ꮯ',dlo:'Ꮰ',dlu:'Ꮱ',dlv:'Ꮲ',tsa:'Ꮳ',tse:'Ꮴ',tsi:'Ꮵ',tso:'Ꮶ',tsu:'Ꮷ',tsv:'Ꮸ',dsa:'Ꮳ',dse:'Ꮴ',dsi:'Ꮵ',dso:'Ꮶ',dsu:'Ꮷ',dsv:'Ꮸ',wa:'Ꮹ',we:'Ꮺ',wi:'Ꮻ',wo:'Ꮼ',wu:'Ꮽ',wv:'Ꮾ',ya:'Ꮿ',ye:'Ᏸ',yi:'Ᏹ',yo:'Ᏺ',yu:'Ᏻ',yv:'Ᏼ'},l={s:'Ꮝ',tl:1,dl:1,ts:1,ds:1,qu:1,kw:1,gw:1,hn:1};this.charProcessor=function(o,O){if(o=='\u0008'){if(O.length){return[O.slice(0,-1),O.length-1]}}else if(i.test(o)){return I[O+o]||[O+o,0]}else{var Q=O+o,_,c;if(_=I[Q]){return[_,0]}else if(_=l[Q]){switch(_){case 1:return[Q,2];default:return[_,1]}}else if(_=I[O]){if(c=I[o])return[_+c,1];else return[_+o,1]}else{return[O+(I[o]||l[o]||o),1]}}}};
