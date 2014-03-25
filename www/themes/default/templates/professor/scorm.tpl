
        
        {*moduleScormOptions: SCORM options page*}
        {capture name = "moduleScormOptions"}
                                <tr><td class = "moduleCell">
                        {if $smarty.get.scorm_review}
                            {assign var = "title" value = "`$title`&nbsp;&raquo;&nbsp;<a class = 'titleLink'  href = '`$smarty.server.PHP_SELF`?ctg=scorm&scorm_review=1'>`$smarty.const._SCORMREVIEW`</a>"}
                            {capture name = 'scorm_review_code'}
<!--ajax:scormUsersTable-->
                                            <table style = "width:100%" class = "sortedTable" size = "{$T_USERS_SIZE}" sortBy = "0" id = "scormUsersTable" useAjax = "1" rowsPerPage = "{$smarty.const.G_DEFAULT_TABLE_SIZE}" url = "professor.php?ctg=scorm&scorm_review=1&">
                                                <tr class = "defaultRowHeight">
                                                    <td class = "topTitle" name = "users_LOGIN">{$smarty.const._USERCAPITAL}</td>
                                                    <td class = "topTitle" name = "content_name">{$smarty.const._UNIT}</td>
                                                    <td class = "topTitle" name = "timestamp">{$smarty.const._DATE}</td>
                                                    <td class = "topTitle" name = "entry">{$smarty.const._ENTRY}</td>
                                                    <td class = "topTitle" name = "lesson_status">{$smarty.const._STATUS}</td>
                                                    <td class = "topTitle centerAlign" name = "total_time">{$smarty.const._TOTALTIME}</td>
                                                    <td class = "topTitle centerAlign" name = "minscore">{$smarty.const._MINSCORE}</td>
                                                    <td class = "topTitle centerAlign" name = "maxscore">{$smarty.const._MAXSCORE}</td>
                                                    <td class = "topTitle centerAlign" name = "masteryscore">{$smarty.const._MASTERYSCORE}</td>
                                                    <td class = "topTitle centerAlign" name = "score">{$smarty.const._SCORE}</td>
                                                {if !isset($T_CURRENT_USER->coreAccess.content) || $T_CURRENT_USER->coreAccess.content == 'change'}
                                                    <td class = "topTitle centerAlign noSort">{$smarty.const._FUNCTIONS}</td>
                                                {/if}
                                                </tr>

                                        {foreach name = 'scorm_data' item = "item" key = "key" from = $T_SCORM_DATA}
                                            <tr class = "{cycle values = "oddRowColor, evenRowColor"} defaultRowHeight">
                                                <td>#filter:login-{$item.users_LOGIN}#</td>
                                                <td>{$item.content_name|eF_truncate:30}</td>
                                                <td style = "white-space:nowrap">#filter:timestamp_time-{$item.timestamp}#</td>
                                                <td>{$item.entry}</td>
                                                <td>{$item.lesson_status}</td>
                                                <td class = "centerAlign">{$item.total_time}</td>
                                                <td class = "centerAlign">{if isset($item.minscore)} #filter:score-{$item.minscore}#%{/if}</td>
                                                <td class = "centerAlign">#filter:score-{$item.maxscore}#%</td>
                                                <td class = "centerAlign">{if $item.masteryscore} #filter:score-{$item.masteryscore}#%{/if}</td>
                                                <td class = "centerAlign">#filter:score-{$item.score}#%</td>
                                            {if !isset($T_CURRENT_USER->coreAccess.content) || $T_CURRENT_USER->coreAccess.content == 'change'}
                                                <td class = "centerAlign"><a href = "javascript:void(0)" onclick = "deleteData(this, {$item.id})"><img src = "images/16x16/error_delete.png" alt = "{$smarty.const._DELETEDATA}" title = "{$smarty.const._DELETEDATA}" border = "0"></a></td>
                                            {/if}
                                            </tr>
                                        {foreachelse}
                                            <tr class = "oddRowColor defaultRowHeight"><td colspan = "100%" class = "emptyCategory">{$smarty.const._NODATAFOUND}</td></tr>
                                        {/foreach}
                                            </table>
<!--/ajax:scormUsersTable-->
                                            <script>
                                            {literal}
                                            function deleteData(el, id) {
                                                Element.extend(el);
                                                url = 'professor.php?ctg=scorm&scorm_review=1&delete='+id;
                                                el.down().src = 'images/others/progress1.gif';
                                                new Ajax.Request(url, {
                                                        method:'get',
                                                        asynchronous:true,
                                                        onFailure: function (transport) {
                                                            el.down().writeAttribute({src:'images/16x16/error_delete.png', title: transport.responseText}).hide();
                                                            new Effect.Appear(el.down().identify());
                                                            window.setTimeout('Effect.Fade("'+el.down().identify()+'")', 10000);
                                                        },
                                                        onSuccess: function (transport) {
                                                            new Effect.Fade(el.up().up());
                                                            }
                                                    });

                                            }
                                            {/literal}
                                            </script>
                            {/capture}
                            {eF_template_printBlock title = $smarty.const._REVIEWSCORMDATAFOR|cat:' &quot;'|cat:$T_CURRENT_LESSON->lesson.name|cat:'&quot;' data = $smarty.capture.scorm_review_code image = '32x32/scorm.png' main_options = $T_TABLE_OPTIONS}

                        {elseif $smarty.get.scorm_import}
                            {assign var = "title" value = "`$title`&nbsp;&raquo;&nbsp;<a class = 'titleLink'  href = '`$smarty.server.PHP_SELF`?ctg=scorm&scorm_import=1'>`$smarty.const._SCORMIMPORT`</a>"}

                            {capture name = 'scorm_import_code'}
                                {$T_UPLOAD_SCORM_FORM.javascript}
                                <form {$T_UPLOAD_SCORM_FORM.attributes}>
                                    {$T_UPLOAD_SCORM_FORM.hidden}
                                    <table style = "margin-top:15px;">
                                        <tr><td class = "labelCell">{$smarty.const._UPLOADTHESCORMFILEINZIPFORMAT}:
                                            <td class = "elementCell">{$T_UPLOAD_SCORM_FORM.scorm_file.html}</td></tr>
                                        <tr><td></td>
                                            <td class = "infoCell">{$smarty.const._EACHFILESIZEMUSTBESMALLERTHAN} <b>{$T_MAX_FILE_SIZE}</b> {$smarty.const._KB}</td></tr>
                                        <tr><td class = "labelCell">{$T_UPLOAD_SCORM_FORM.url_upload.label}:
                                            <td class = "elementCell">{$T_UPLOAD_SCORM_FORM.url_upload.html}</td></tr>
                                        <tr><td></td><td>&nbsp;</td></tr>
                                        <tr><td class = "labelCell"></td>
                                            <td class = "elementCell">{$T_UPLOAD_SCORM_FORM.submit_upload_scorm.html}</td></tr>
                                    </table>
                                </form>
                            {/capture}
                            {eF_template_printBlock title = $smarty.const._SCORMIMPORT data = $smarty.capture.scorm_import_code image = '32x32/scorm.png' main_options = $T_TABLE_OPTIONS}

                        {elseif $smarty.get.scorm_export}
                            {assign var = "title" value = "`$title`&nbsp;&raquo;&nbsp;<a class = 'titleLink'  href = '`$smarty.server.PHP_SELF`?ctg=scorm&scorm_export=1'>`$smarty.const._SCORMEXPORT`</a>"}

                            {capture name = 'scorm_export_code'}
                            {if (isset($T_SCORM_EXPORT_FILE))}
                                <table style = "margin-top:15px;">
                                    <tr>
                                        <td><span style = "vertical-align:middle">{$smarty.const._DOWNLOADSCORMEXPORTEDFILE}:&nbsp;</span>
                                            <a href = "view_file.php?file={$T_SCORM_EXPORT_FILE.path}&action=download" target = "POPUP_FRAME" style = "vertical-align:middle">{$T_SCORM_EXPORT_FILE.name}</a>
                                            <img src = "images/16x16/import.png" alt = "{$smarty.const._DOWNLOADFILE}" title = "{$smarty.const._DOWNLOADFILE}" border = "0" style = "vertical-align:middle">
                                        </td>
                                    </tr>
                                </table>
                            {/if}
                                    {$T_EXPORT_SCORM_FORM.javascript}
                                    <form {$T_EXPORT_SCORM_FORM.attributes}>
                                        {$T_EXPORT_SCORM_FORM.hidden}
                                        <table style = "margin-top:15px;">
                                            <tr>
                                                <td class = "labelCell">{$smarty.const._SCORMEXPORT}:&nbsp;</td>
                                                <td class = "elementCell">{$T_EXPORT_SCORM_FORM.submit_export_scorm.html}</td>
                                                </tr>
                                        </table>
                                    </form>
                            {/capture}
                            {eF_template_printBlock title = $smarty.const._SCORMEXPORT data = $smarty.capture.scorm_export_code image = '32x32/scorm.png' main_options = $T_TABLE_OPTIONS}

                        {else}
                            {capture name = 't_scorm_tree_code'}
                                <script>
                                {literal}
                                    function convertScorm(el, id) {
                                        Element.extend(el);
                                        if (el.up().previous().previous().src.match('scorm_test')) {
                                            newSrc = 'images/drag-drop-tree/scorm.png';
                                            url    = 'professor.php?ctg=scorm&set_type=scorm&id='+id;
                                            button = 'images/16x16/scorm_to_test.png';
                                        } else {
                                            newSrc = 'images/drag-drop-tree/scorm_test.png';
                                            url    = 'professor.php?ctg=scorm&set_type=scorm_test&id='+id;
                                            button = 'images/16x16/test_to_scorm.png';
                                        }
                                        el.down().src = 'images/others/progress1.gif';

                                        new Ajax.Request(url, {
                                                method:'get',
                                                asynchronous:true,
                                                onFailure: function (transport) {
                                                    el.down().writeAttribute({src:'images/16x16/error_delete.png', title: transport.responseText}).hide();
                                                    new Effect.Appear(el.down().identify());
                                                    window.setTimeout('Effect.Fade("'+el.down().identify()+'")', 10000);
                                                },
                                                onSuccess: function (transport) {
                                                    el.up().previous().previous().src = newSrc;
                                                    el.down().src = button;
                                                    img    = new Element('img', {src:'images/16x16/success.png'}).setStyle({verticalAlign:'middle'}).hide();
                                                    el.up().insert(img);
                                                    new Effect.Appear(img.identify());
                                                    window.setTimeout('Effect.Fade("'+img.identify()+'")', 2500);
                                                    }
                                            });
                                    }
                                {/literal}
                                </script>
                                <div id = "expand_collapse_div" expand = "true">
                                    <b><a id = "expand_collapse_link" href = "javascript:void(0)" onclick = "expandCollapse(this)">{$smarty.const._EXPANDALL}</a></b><br/>
                                </div>
                                <table>
                                    <tr><td>
                                        <ul id = "dhtmlContentTree" class = "dhtmlgoodies_tree">
                                            {$T_SCORM_TREE}
                                        </ul>
                                    </td></tr>
                                </table>

                            {/capture}
                            {eF_template_printBlock title = $smarty.const._SCORMOPTIONSFOR|cat:' &quot;'|cat:$T_CURRENT_LESSON->lesson.name|cat:'&quot;' data = $smarty.capture.t_scorm_tree_code image = '32x32/scorm.png' main_options = $T_TABLE_OPTIONS}
                        {/if}
                                </td></tr>
        {/capture}
