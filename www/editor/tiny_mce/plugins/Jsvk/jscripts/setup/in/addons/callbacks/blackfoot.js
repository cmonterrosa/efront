/**
 * $Id$
 *
 * Blackfoot char processor
 *
 * This software is protected by patent No.2009611147 issued on 20.02.2009 by Russian Federal Service for Intellectual Property Patents and Trademarks.
 *
 * @author Konstantin Wiolowan
 * @copyright 2008-2009 Konstantin Wiolowan <wiolowan@mail.ru>
 * @version $Rev$
 * @lastchange $Author$ $Date$
 */
new function(){var i= /[^aehikmnopstwy]/,I={i:'ᖱ','ᐤi':'ᑯ','ᐨi':'ᒧ','ᘁi':'ᖽ','ᐢi':'ᒍ','ᐡi':'ᖹ','ᔈi':'ᓱ',yi:'ᔪ',wi:'ᖵ','ᖳi':'ᖳᐟ','ᖰi':'ᖰᐟ','ᖲi':'ᖲᐟ','ᑫi':'ᑫᐟ','ᑭi':'ᑭᐟ','ᑲi':'ᑲᐟ','ᒣi':'ᒣᐟ','ᒥi':'ᒥᐟ','ᒪi':'ᒪᐟ','ᖿi':'ᖿᐟ','ᖼi':'ᖼᐟ','ᖾi':'ᖾᐟ','ᒉi':'ᒉᐟ','ᒋi':'ᒋᐟ','ᒐi':'ᒐᐟ','ᖻi':'ᖻᐟ','ᖸi':'ᖸᐟ','ᖺi':'ᖺᐟ','ᓭi':'ᓭᐟ','ᓯi':'ᓯᐟ','ᓴi':'ᓴᐟ','ᔦi':'ᔦᐟ','ᔨi':'ᔨᐟ','ᔭi':'ᔭᐟ','ᖷi':'ᖷᐟ','ᖴi':'ᖴᐟ','ᖶi':'ᖶᐟ','ᖳo':'ᖳᐠ','ᖰo':'ᖰᐠ','ᑫo':'ᑫᐠ','ᑭo':'ᑭᐠ','ᒣo':'ᒣᐠ','ᒥo':'ᒥᐠ','ᖿo':'ᖿᐠ','ᖼo':'ᖼᐠ','ᒉo':'ᒉᐠ','ᒋo':'ᒋᐠ','ᖻo':'ᖻᐠ','ᖸo':'ᖸᐠ','ᓭo':'ᓭᐠ','ᓯo':'ᓯᐠ','ᔦo':'ᔦᐠ','ᔨo':'ᔨᐠ','ᖷo':'ᖷᐠ','ᖴo':'ᖴᐠ'},l={a:'ᖳ',e:'ᖰ',o:'ᖲ','ᐤa':'ᑫ','ᐤe':'ᑭ','ᐤo':'ᑲ','ᐨa':'ᒣ','ᐨe':'ᒥ','ᐨo':'ᒪ','ᘁa':'ᖿ','ᘁe':'ᖼ','ᘁo':'ᖾ','ᐢa':'ᒉ','ᐢe':'ᒋ','ᐢo':'ᒐ','ᐡa':'ᖻ','ᐡe':'ᖸ','ᐡo':'ᖺ','ᔈa':'ᓭ','ᔈe':'ᓯ','ᔈo':'ᓴ',ya:'ᔦ',ye:'ᔨ',yo:'ᔭ',wa:'ᖷ',we:'ᖴ',wo:'ᖶ','ᐤy':'ᐤy','ᐨy':'ᐨy','ᘁy':'ᘁy','ᐢy':'ᐢy','ᐡy':'ᐡy','ᔈy':'ᔈy','ᐤs':'ᐤs','ᐨs':'ᐨs','ᘁs':'ᘁs','ᐢs':'ᐢs','ᐡs':'ᐡs','ᔈs':'ᔈs','ᐤw':'ᐤw','ᐨw':'ᐨw','ᘁw':'ᘁw','ᐢw':'ᐢw','ᐡw':'ᐡw','ᔈw':'ᔈw',p:'ᐤ',t:'ᐨ',k:'ᘁ',m:'ᐢ',n:'ᐡ',s:'ᔈ',h:'ᑊ','ᑊk':'ᐦ'},o={'ᖲo':'ᖲᖲ','ᑲo':'ᑲᖲ','ᒪo':'ᒪᖲ','ᖾo':'ᖾᖲ','ᒐo':'ᒐᖲ','ᖺo':'ᖺᖲ','ᓴo':'ᓴᖲ','ᔭo':'ᔭᖲ','ᖶo':'ᖶᖲ'};this.charProcessor=function(O,Q){if(O=='\u0008'){if(Q.length){return[Q.slice(0,-1),Q.length-1]}}else if(i.test(O)){return I[Q+O]||[Q+O,0]}else{var _,c,C,e='';if(Q.charAt(0)=='ᐦ'){e='ᑊ';Q='ᘁ'}_=Q+O;if(c=I[_]){return[e+c,0]}else if(c=l[_]){return[e+c,c.length]}else if(c=o[_]){return[e+c,1]}else if(c=l[Q]){if(/[ᐤᐨᘁᐢᐡᔈ][syw][aeio]/.test(_)){c=_.charAt(0)+_.charAt(2);return([e+(I[c]||l[c])+{s:'ᐧ',y:'ᑉ',w:'='}[_.charAt(1)],0])}if(C=I[O])return[c+C,1];else return[e+c+O,1]}else{return[e+Q+(I[O]||l[O]||O),1]}}}};
