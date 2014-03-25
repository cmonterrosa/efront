<?php
/**
 *
 */

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}

/**
 *
 * @author user
 *
 */
class EfrontScorm
{
    /**
     * Parse SCORM manifest XML file
     *
     * @param $data
     * @return unknown_type
     */
    public static function parseManifest($data) {
        //We don't use SimpleXML, due to memory and other issues with this iterator class
        $parser   = xml_parser_create();

        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, $data, $tagContents, $tags);
        xml_parser_free($parser);

        $currentParent = array(0 => 0);

        for ($i = 0; $i < sizeof($tagContents); $i++) {
            if ($tagContents[$i]['type'] != 'close') {
                $tagArray[$i] = array('parent_index' => end($currentParent),
                                      'tag'          => $tagContents[$i]['tag'],
                                      'value'        => isset($tagContents[$i]['value']) ? $tagContents[$i]['value'] : null,
                                      'attributes'   => isset($tagContents[$i]['attributes']) ? $tagContents[$i]['attributes'] : null,
                                      'children'     => array()
                );
                array_push($tagArray[end($currentParent)]['children'], $i);
            }
            if ($tagContents[$i]['type'] == 'open') {
                array_push($currentParent, $i);
            } else if ($tagContents[$i]['type'] == 'close') {
                array_pop($currentParent);
            }

        }
        return $tagArray;
    }


    /**
     * Copy node children
     * This function is used to recursively copy a node to the end of the $tagArray, along
     * with its children. Its children are copied as new entries in the end of the $tagArray
     * array, and the entries of the 'children' field are updated accordingly with the new keys
     * @param $tagArray The tag array, passed by reference to eliminate the need for returning it
     * @param $node The key of the tagArray entry to copy
     * @param $parent The key of the tagArray entry which will be the father of the new node
     */
    private static function copyNodeChildren(&$tagArray, $node, $parent) {
        $newRule = $tagArray[$node];
        $newRule['parent_index'] = $parent;
        $tagArray[] = $newRule;
        end($tagArray);
        $newParent = key($tagArray);
        $tagArray[$parent]['children'][] = $newParent;
        foreach ($newRule['children'] as $key => $value) {
            //Remove original children from this node
            unset($tagArray[$newParent]['children'][$key]);
            //         //Recursively copy children to the new node as well
            self :: copyNodeChildren($tagArray, $value, $newParent);
        }
    }


    /**
     *
     * @param unknown_type $file
     * @return unknown_type
     */
    public static function import($lesson, $manifestFile, $scormFolderName, $parameters, $iframe_parameters) {
        if ($lesson instanceof EfrontLesson) {
            $currentLesson = $lesson;
        } else {
            $currentLesson = new EfrontLesson($lesson);
        }
        $lessons_ID     = $currentLesson -> lesson['id'];
        $currentContent = new EfrontContentTree($currentLesson);

        $manifestXML  = file_get_contents($manifestFile['path']);
        $tagArray     = EfrontScorm :: parseManifest($manifestXML);

		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
		    if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
	            /**
	             * We must merge sequencingCollection general rules with local sequencing rules
	             * The rule is the following: There can be 0 or 1 <imsss:sequencingCollection> tags in the end of the manifest.
	             * If one exists, it may contain 1 or more <imsss:sequencing> tags, each with an ID, like:
	             * <imsss:sequencing ID = "seqCol-CM07d-1">
	             * Each of these will contain inner rules, for example:
	             *   <imsss:sequencing ID = "seqCol-CM07d-3">
	             *		<imsss:limitConditions attemptLimit="1"/>
	             *      <imsss:rollupRules rollupObjectiveSatisfied="false"/>
	             *   </imsss:sequencing>
	             * Now, for every <item> element in the manifest, there may be inline <imsss:sequencing> declarations. These may specify
	             * <imsss:XXX> rules like above, that have local scope, or they may be having the IDRef attribute, pointing to a sequencingCollection's
	             * <imsss:sequencing>, or both. In the last case, the <imsss:XXX> rules must be merged for each item. In the case that a rule exists
	             * in both parts, the inline <item>'s rule takes precedence.
	             *
	             * The code below does this merge:
	             * 1. Parse the manifest array to find the general <sequencingCollection> rules.
	             * 2. Get all the rules contained in the collection, in an ID => <imsss rules> array
	             * 3. Walk through the manifest array to find all <item> elements that reference an ID. Get the existing <imsss> rules it might
	             *    have. Merge the general collection rules with local, but bypass those that already exist
	             *
	             */
	            //$collections array holds the sequencing tags that are in the manifest, as keys in the array
	            $collections = array();
	            foreach ($tagArray as $key => $value) {
	                if (strcasecmp($value['tag'], 'IMSSS:SEQUENCINGCOLLECTION') === 0) {
	                    $sequencingCollection = $key;
	                    $collections = array_merge($collections, $value['children']);
	                }
	            }

	            //$rules is an array that holds subarrays, where each has a key that is the ID (for example, 'seqCol-CM07d-3')
	            //and its values are the keys of the rules, for example array(132,133,134)
	            $rules = array();
	            foreach ($collections as $key => $sequencing) {
	                $node       = $tagArray[$sequencing];
	                $id         = EfrontContentTreeSCORM :: form_id($node['attributes']['ID']);
	                $rules[$id] = $node['children'];

	            }


	            //Parse the manifest to get the <imsss:sequencing> rules (that are not inside the $collections)
	            foreach ($tagArray as $key => $value) {
	                if (strcasecmp($value['tag'], 'IMSSS:SEQUENCING') === 0 && !in_array($key, $collections)) {
	                    //Check whether this rule references an item in the collection
	                    if (in_array(EfrontContentTreeSCORM :: form_id($id), array_keys($rules))) {

	                        $tagArray[] = array('tag'=>'IMSSS:SEQUENCING', 'parent_index'=>$value['parent_index']);

	                        //end($tagArray);
	                        //$tagArray[$key]['children'][] = key($tagArray);

	                        //Get the existing rules of the sequencing, to compare them later with referenced ones
	                        $existingRules = array();
	                        foreach ($value['children'] as $inrule) {
	                            $existingRules[] = $tagArray[$inrule]['tag'];
	                        }
	                        //echo "<br>----------Existing----------------<br>";
	                        //pr($existingRules);
	                        //Compare referenced rules with local. If they don't overlap, create a new node in the tagArray, and set him to be
	                        //referenced by the item's sequencing

	                        //echo "<br>----------Collection----------------<br>";

	                        //pr($rules);
	                        //pr($existingRules);
	                        foreach($rules[$value['attributes']['IDREF']] as $rule) {
	                            if (!in_array($tagArray[$rule]['tag'], $existingRules)) {
	                                self :: copyNodeChildren($tagArray, $rule, $key);
	                                /*
	                                 $newRule = $tagArray[$rule];
	                                 $newRule['parent_index'] = $key;
	                                 $tagArray[] = $newRule;
	                                 end($tagArray);
	                                 $tagArray[$key]['children'][] = key($tagArray);
	                                 */
	                                /*
	                                 $part1 = (array_slice($tagArray, 0, $count, true));
	                                 $part2 = (array_slice($tagArray, $count, -1, true));
	                                 $tagArray = ($part1 + array($k => $tagArray[$k]) + $part2);
	                                 */
	                            }
	                        }
	                    }
	                }
	            }

	            /**
	             * We need to unset all the sequencingCollection rules, since they tend to mess up with <item>'s rules in
	             * complete XML parsing below. So, this piece of code finds the sequencingCollection (1 at most), and recursively
	             * unsets it and all of its children from the $tagArray, as if they never existed.
	             */
	            if ($sequencingCollection) {
	                $removeNode = $tagArray[$sequencingCollection];
	                $children   = array($sequencingCollection);
	                $count = 1;

	                while (sizeof($children) > 0 && $count < 1000) {
	                    $children   = array_merge($children, $removeNode['children']);
	                    $removeNode = $tagArray[$children[$count]];
	                    unset($tagArray[$children[$count++]]);
	                }
	                unset($tagArray[$sequencingCollection]);
	            }
	        } #cpp#endif
	    } #cpp#endif

        /**
         * Now parse XML file as usual
         */
        foreach($tagArray as $key => $value) {
            $fields = array();

            switch ($value['tag']) {
                case 'SCHEMAVERSION':
                    $scormVersion = $value['value'];
                    if (stripos($scormVersion, '2004') !== false && (G_VERSIONTYPE == 'community' || G_VERSIONTYPE == 'standard')) { //This additional line is used in case we have the community edition
                        throw new EfrontContentException(_SCORM2004NOTSUPPORTED, EfrontContentException::UNSUPPORTED_CONTENT);
                    }
                    if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
                        if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
                            $scorm2004    = in_array($scormVersion, EfrontContentTreeSCORM :: $scorm2004Versions);
                        } #cpp#endif
                    } #cpp#endif
                    break;
                case 'TITLE':
                    $cur = $value['parent_index'];
                    $total_fields[$cur]['name'] = $value['value'] ? $value['value'] : " ";
                    break;
                case 'ORGANIZATION':

                    $item_key = $key;
                    if ($scorm2004) {
	                    $total_fields[$key]['lessons_ID']		= $lessons_ID;
	                    $total_fields[$key]['timestamp']		= time();
	                    $total_fields[$key]['ctg_type']			= 'scorm';
	                    $total_fields[$key]['active']			= 1;
	                    $total_fields[$key]['scorm_version']	= $scormVersion;
	                    $total_fields[$key]['identifier'] = $value['attributes']['IDENTIFIER'];

	                    $organizations[$key]['id']							= $value['attributes']['IDENTIFIER'];
	                    $organizations[$key]['structure']					= $value['attributes']['STRUCTURE'];
	                    $organizations[$key]['objectives_global_to_system']	= $value['attributes']['ADLSEQ:OBJECTIVESGLOBALTOSYSTEM'];
	                    $organizations[$key]['shared_data_global_to_system'] = $value['attributes']['ADLCP:SHAREDDATAGLOBALTOSYSTEM'];

	                    $organization = $value['attributes']['IDENTIFIER'];

	                    $hide_lms_ui[$key]['is_visible'] = $value['attributes']['ISVISIBLE'];

	                    $content_to_organization[$item_key] = $organization;
                    }
                    break;
                case 'ITEM':
                    $item_key = $key;
                    $total_fields[$key]['lessons_ID'] = $lessons_ID;
                    $total_fields[$key]['timestamp']  = time();
                    $total_fields[$key]['ctg_type']   = 'scorm';
                    $total_fields[$key]['active']     = 1;
                    $total_fields[$key]['scorm_version']   = $scormVersion;
                    $total_fields[$key]['identifier'] = $value['attributes']['IDENTIFIER'];

                    $hide_lms_ui[$key]['is_visible'] = $value['attributes']['ISVISIBLE'];

                    if ($scorm2004) {
                        $references[$key]['IDENTIFIERREF'] = EfrontContentTreeSCORM :: form_id($value['attributes']['IDENTIFIERREF']);

                        /*SCORM 2004: params in element items must be appended to the url*/
                        $references[$key]['PARAMETERS'] = $value['attributes']['PARAMETERS'];
                    } else {
                        $references[$key]['IDENTIFIERREF'] = $value['attributes']['IDENTIFIERREF'];
                        $references[$key]['PARAMETERS'] = $value['attributes']['PARAMETERS'];
                    }
                    $content_to_organization[$item_key] = $organization;
                    break;
                case 'RESOURCE':
                    if($scorm2004) {
                        $resources[$key] =  EfrontContentTreeSCORM :: form_id($value['attributes']['IDENTIFIER']);
                    } else {
                        $resources[$key] =  $value['attributes']['IDENTIFIER'];
                    }
                    break;
                case 'FILE':
                    $files[$key] = $value['attributes']['HREF'];
                    break;
                case 'ADLCP:MAXTIMEALLOWED':
                    $maxtimeallowed[$key]  = $value['value'];
                    break;
                case 'ADLCP:TIMELIMITACTION':
                    $timelimitaction[$key] = $value['value'];
                    break;
                case 'ADLCP:MASTERYSCORE':
                    $masteryscore[$key]    = $value['value'];
                    break;
                case 'ADLCP:DATAFROMLMS':
                    $datafromlms[$key]     = $value['value'];
                    break;
                case 'ADLCP:PREREQUISITES':
                    $prerequisites[$key]   = $value['value'];
                    break;
                case 'ADLCP:COMPLETIONTHRESHOLD':
                    $completion_threshold[$item_key][$key]['min_progress_measure']	= $value['attributes']['MINPROGRESSMEASURE'];
                    $completion_threshold[$item_key][$key]['completed_by_measure']	= $value['attributes']['COMPLETEDBYMEASURE'];
                    $completion_threshold[$item_key][$key]['progress_weight']		= $value['attributes']['PROGRESSWEIGHT'];
                    break;
                case 'IMSSS:SEQUENCING':
                    $item_key = $value['parent_index'];
                    break;
                case 'IMSSS:LIMITCONDITIONS':
                    $limit_conditions[$item_key][$key]['attempt_limit']						= $value['attributes']['ATTEMPTLIMIT'];
                    $limit_conditions[$item_key][$key]['attempt_absolute_duration_limit']	= $value['attributes']['ATTEMPTABSOLUTEDURATIONLIMIT'];
                    break;
                case 'IMSSS:ROLLUPRULES':
                    $rollup_controls[$item_key][$key]['rollup_objective_satisfied']			= $value['attributes']['ROLLUPOBJECTIVESATISFIED'];
                    $rollup_controls[$item_key][$key]['rollup_objective_measure_weight']	= $value['attributes']['OBJECTIVEMEASUREWEIGHT'];
                    $rollup_controls[$item_key][$key]['rollup_progress_completion']			= $value['attributes']['ROLLUPPROGRESSCOMPLETION'];
                    break;
                case 'ADLSEQ:ROLLUPCONSIDERATIONS':
                    $rollup_considerations[$item_key][$key]['required_for_satisfied']			= $value['attributes']['REQUIREDFORSATISFIED'];
                    $rollup_considerations[$item_key][$key]['required_for_not_satisfied']		= $value['attributes']['REQUIREDFORNOTSATISFIED'];
                    $rollup_considerations[$item_key][$key]['required_for_completed']			= $value['attributes']['REQUIREDFORCOMPLETED'];
                    $rollup_considerations[$item_key][$key]['required_for_incomplete']			= $value['attributes']['REQUIREDFORINCOMPLETE'];
                    $rollup_considerations[$item_key][$key]['measure_satisfaction_if_active']	= $value['attributes']['MEASURESATISFACTIONIFACTIVE'];
                    break;
				case 'IMSSS:PRECONDITIONRULE':
                    $cond_key = $key;
					$rule_conditions[$item_key][$cond_key]['rule_type'] =  0;

                    break;
                case 'IMSSS:POSTCONDITIONRULE':
                    $cond_key = $key;
                    $rule_conditions[$item_key][$cond_key]['rule_type'] =  1;
                    break;
                case 'IMSSS:EXITCONDITIONRULE':
                    $cond_key = $key;
                    $rule_conditions[$item_key][$cond_key]['rule_type'] =  2;
                    break;
                case 'IMSSS:RULECONDITIONS':
                    $rule_conditions[$item_key][$cond_key]['condition_combination'] = $value['attributes']['CONDITIONCOMBINATION'];
                    break;
                case 'IMSSS:RULEACTION':
                    $rule_conditions[$item_key][$cond_key]['rule_action'] = $value['attributes']['ACTION'];
                    break;
                case 'IMSSS:RULECONDITION':
                    $rule_condition[$cond_key][$key]['referenced_objective']	= $value['attributes']['REFERENCEDOBJECTIVE'];
                    $rule_condition[$cond_key][$key]['measure_threshold']		= $value['attributes']['MEASURETHRESHOLD'];
                    $rule_condition[$cond_key][$key]['operator']				= $value['attributes']['OPERATOR'];
                    $rule_condition[$cond_key][$key]['condition']				= $value['attributes']['CONDITION'];
                    break;
                case 'IMSSS:PRIMARYOBJECTIVE':
                    $obj_key = $key;
                    $objective_ID 	= $value['attributes']['OBJECTIVEID'];

                    $objective[$item_key][$obj_key]['is_primary'] 			= '1';
                    $objective[$item_key][$obj_key]['satisfied_by_measure'] = $value['attributes']['SATISFIEDBYMEASURE'];

                    /*
                     if($objective_ID == '') {
                     $objective_ID = 'empty_obj_id';
                     }
                     */

					$objective[$item_key][$obj_key]['objective_ID'] = 	$objective_ID;
					//pr($objective);

                    break;
                case 'IMSSS:OBJECTIVE':
                    $obj_key = $key;
                    $objective_ID 	= $value['attributes']['OBJECTIVEID'];

                    $objective[$item_key][$obj_key]['is_primary'] 			= '0';
                    $objective[$item_key][$obj_key]['satisfied_by_measure'] = $value['attributes']['SATISFIEDBYMEASURE'];
                    $objective[$item_key][$obj_key]['objective_ID'] 		= $value['attributes']['OBJECTIVEID'];
                    break;
                case 'IMSSS:MINNORMALIZEDMEASURE':
                    $objective[$item_key][$obj_key]['min_normalized_measure'] = $value['value'];
                    break;
                case 'IMSSS:MAPINFO':
                    $map_info[$item_key][$key]['objective_ID']				= $objective_ID;
                    $map_info[$item_key][$key]['target_objective_ID'] 		= $value['attributes']['TARGETOBJECTIVEID'];
                    $map_info[$item_key][$key]['read_satisfied_status'] 	= $value['attributes']['READSATISFIEDSTATUS'];
                    $map_info[$item_key][$key]['read_normalized_measure'] 	= $value['attributes']['READNORMALIZEDMEASURE'];
                    $map_info[$item_key][$key]['write_satisfied_status'] 	= $value['attributes']['WRITESATISFIEDSTATUS'];
                    $map_info[$item_key][$key]['write_normalized_measure'] 	= $value['attributes']['WRITENORMALIZEDMEASURE'];
                    break;
                case 'ADLSEQ:OBJECTIVE':
                    $objective_ID 	= $value['attributes']['OBJECTIVEID'];
                    break;

                case 'ADLSEQ:MAPINFO':
                    $adl_seq_map_info[$item_key][$key]['objective_ID']				= $objective_ID;
                    $adl_seq_map_info[$item_key][$key]['target_objective_ID'] 		= $value['attributes']['TARGETOBJECTIVEID'];

                    $adl_seq_map_info[$item_key][$key]['read_raw_score']			= $value['attributes']['READRAWSCORE'];
                    $adl_seq_map_info[$item_key][$key]['read_min_score']			= $value['attributes']['READMINSCORE'];
                    $adl_seq_map_info[$item_key][$key]['read_max_score']			= $value['attributes']['READMAXSCORE'];
                    $adl_seq_map_info[$item_key][$key]['read_completion_status']	= $value['attributes']['READCOMPLETIONSTATUS'];
                    $adl_seq_map_info[$item_key][$key]['read_progress_measure'] 	= $value['attributes']['READPROGRESSMEASURE'];

                    $adl_seq_map_info[$item_key][$key]['write_raw_score']			= $value['attributes']['WRITERAWSCORE'];
                    $adl_seq_map_info[$item_key][$key]['write_min_score']			= $value['attributes']['WRITEMINSCORE'];
                    $adl_seq_map_info[$item_key][$key]['write_max_score']			= $value['attributes']['WRITEMAXSCORE'];
                    $adl_seq_map_info[$item_key][$key]['write_completion_status']	= $value['attributes']['WRITECOMPLETIONSTATUS'];
                    $adl_seq_map_info[$item_key][$key]['write_progress_measure'] 	= $value['attributes']['WRITEPROGRESSMEASURE'];
                    break;
                case 'IMSSS:ROLLUPRULE':
                    $rollup_rule_key = $key;

                    $rollup_rules[$item_key][$key]['child_activity_set'] 	= $value['attributes']['CHILDACTIVITYSET'];
                    $rollup_rules[$item_key][$key]['minimum_count'] 		= $value['attributes']['MINIMUMCOUNT'];
                    $rollup_rules[$item_key][$key]['minimum_percent'] 		= $value['attributes']['MINIMUMPERCENT'];
                    $rollup_rules[$item_key][$key]['action'] 				= $value['attributes']['ACTION'];
                    break;
                case 'IMSSS:ROLLUPCONDITIONS':
                    $rollup_rules[$item_key][$rollup_rule_key]['condition_combination'] = $value['attributes']['CONDITIONCOMBINATION'];
                    break;
                case 'IMSSS:ROLLUPACTION':
                    $rollup_rules[$item_key][$rollup_rule_key]['rule_action'] = $value['attributes']['ACTION'];
                    break;
                case 'IMSSS:ROLLUPCONDITION':
                    $rollup_rule_conditions[$rollup_rule_key][$key]['operator'] 	= $value['attributes']['OPERATOR'];
                    $rollup_rule_conditions[$rollup_rule_key][$key]['condition']	= $value['attributes']['CONDITION'];
                    break;
                case 'ADLNAV:PRESENTATION':
                    $item_key = $value['parent_index'];
                    break;
                case 'ADLNAV:HIDELMSUI':
                    $hide_lms_ui[$item_key][$value['value']] = 'true';
                    break;
                case 'IMSSS:CONTROLMODE':
                    $control_mode[$item_key][$key]['choice']							 = $value['attributes']['CHOICE'];
                    $control_mode[$item_key][$key]['choice_exit']						 = $value['attributes']['CHOICEEXIT'];
                    $control_mode[$item_key][$key]['flow']								 = $value['attributes']['FLOW'];
                    $control_mode[$item_key][$key]['forward_only']						 = $value['attributes']['FORWARDONLY'];
                    $control_mode[$item_key][$key]['use_current_attempt_objective_info'] = $value['attributes']['USECURRENTATTEMPTOBJECTIVEINFO'];
                    $control_mode[$item_key][$key]['use_current_attempt_progress_info']	 = $value['attributes']['USECURRENTATTEMPTPROGRESSINFO'];
                    break;
                case 'ADLSEQ:CONSTRAINEDCHOICECONSIDERATIONS':
                    $constrained_choice[$item_key]['prevent_activation']	= $value['attributes']['PREVENTACTIVATION'];
                    $constrained_choice[$item_key]['constrain_choice']		= $value['attributes']['CONSTRAINCHOICE'];
                    break;
                case 'IMSSS:DELIVERYCONTROLS':
                    $delivery_controls[$item_key][$key]['objective_set_by_content']		= $value['attributes']['OBJECTIVESETBYCONTENT'];
                    $delivery_controls[$item_key][$key]['completion_set_by_content']	= $value['attributes']['COMPLETIONSETBYCONTENT'];
                    $delivery_controls[$item_key][$key]['tracked']						= $value['attributes']['TRACKED'];
                    break;
                case 'ADLCP:MAP':
                    $maps[$item_key][$key]['target_ID'] = $value['attributes']['TARGETID'];
                    $maps[$item_key][$key]['read_shared_data'] = $value['attributes']['READSHAREDDATA'];
                    $maps[$item_key][$key]['write_shared_data'] = $value['attributes']['WRITESHAREDDATA'];
                    break;
                default:
                    break;
            }
        }


	//	exit();
		if (G_VERSIONTYPE != 'community') { #cpp#ifndef COMMUNITY
		    if (G_VERSIONTYPE != 'standard') { #cpp#ifndef STANDARD
	            if ($scorm2004) {
	                foreach ($references as $key => $value) {

	                    $ref = array_search($value['IDENTIFIERREF'], $resources);
	                    if ($ref !== false && !is_null($ref)) {
	                        /*SCORM 2004: The xml:base attribute provides a relative path offset for the content file(s) contained in the manifest*/
	                        $path_offset = $tagArray[$ref]['attributes']['XML:BASE'];

	                        $data = file_get_contents($scormPath."/".$path_offset.$tagArray[$ref]['attributes']['HREF']);

	                        $primitive_hrefs[$ref] = str_replace("\\", "/", $path_offset.$tagArray[$ref]['attributes']['HREF']);
	                        $path_part[$ref]       = dirname($primitive_hrefs[$ref]);

	                        foreach($tagArray[$ref]['children'] as $value2) {
	                            if ($tagArray[$value2]['tag'] == 'DEPENDENCY') {
	                                $idx = array_search($tagArray[$value2]['attributes']['IDENTIFIERREF'], $resources);

	                                foreach ($tagArray[$idx]['children'] as $value3) {
	                                    if ($tagArray[$value3]['tag'] == 'FILE')  {
	                                        $data = preg_replace("#(\.\.\/(\w+\/)*)?".$tagArray[$value3]['attributes']['HREF']."#", $currentLesson -> getDirectory()."/".$scormFolderName.'/'.$path_part[$ref]."/$1".$tagArray[$value3]['attributes']['HREF'], $data);
	                                    }
	                                }
	                            }
	                        }
	                        //$total_fields[$key]['data'] = eF_postProcess(str_replace("'","&#039;",$data));

	                        //$total_fields$adl_seq_map_info[$item_key][$key]['target_objective_ID'[$key]['data'] = '<iframe height = "100%"  width = "100%" frameborder = "no" name = "scormFrameName" id = "scormFrameID" src = "'.G_RELATIVELESSONSLINK.$lessons_ID."/".$scormFolderName.'/'.$primitive_hrefs[$ref]. $value['PARAMETERS']. '" onload = "eF_js_setCorrectIframeSize()"></iframe><iframe name = "commitFrame" frameborder = "no" id = "commitFrame" width = "1" height = "1" style = "display:none"></iframe>';
							//
							//
							//

	                        if ($parameters['embed_type'] == 'iframe') {
	                            $total_fields[$key]['data'] = '<iframe '.$parameters['iframe_parameters'].' name = "scormFrameName" id = "scormFrameID" src = "'.$currentLesson -> getDirectoryUrl()."/".$scormFolderName.'/'.$primitive_hrefs[$ref]. $value['PARAMETERS']. '" onload = "if (window.eF_js_setCorrectIframeSize) {eF_js_setCorrectIframeSize();} else {setIframeSize = true;}"></iframe>';
	                        } else {
	                            $total_fields[$key]['data'] = '
	                            	<div style = "text-align:center;height:300px">
		                            	<span>##CLICKTOSTARTUNIT##</span><br/>
		                        		<input type = "button" value = "##STARTUNIT##" class = "flatButton" onclick = \'window.open("'.$currentLesson -> getDirectoryUrl()."/".urlencode($scormFolderName).'/'.urlencode($primitive_hrefs[$ref]). $value['PARAMETERS'].'", "scormFrameName", "'.$parameters['popup_parameters'].'")\' >
	                        		</div>';
	                        }
	                        /*
	                         $total_fields[$key]['data'] = '
	                         <style>
	                         iframe.scormCommitFrame{width:100%;height:500px;border:1px solid red;}
	                         </style>
	                         <iframe name = "scormFrameName" id = "scormFrameID" class = "scormFrame" src = "'.$currentLesson -> getDirectoryUrl()."/".$scormFolderName.'/'.$primitive_hrefs[$ref]. $value['PARAMETERS']. '" onload = "eF_js_setCorrectIframeSize()"></iframe>
	                         <iframe name = "commitFrame" id = "commitFrame" class = "scormCommitFrame">Sorry, but your browser needs to support iframes to see this</iframe>';

	                         */

	                    }
	                }
	                $lastUnit = $currentContent -> getLastNode();
	                $lastUnit ? $this_id  = $lastUnit['id'] : $this_id = 0;
	                //$this_id = $tree[sizeof($tree) - 1]['id'];

	                foreach ($total_fields as $key => $value)  {

	                    if (isset($value['ctg_type']))  {
	                        $total_fields[$key]['previous_content_ID'] = $this_id;

	                        if (!isset($total_fields[$key]['parent_content_ID'])) {
	                            $total_fields[$key]['parent_content_ID'] = 0;
	                        }

	                        $total_fields[$key]['options'] = serialize(array('hide_navigation' =>1 ,'complete_unit_setting' => EfrontUnit::COMPLETION_OPTIONS_HIDECOMPLETEUNITICON));
	                        $this_id = eF_insertTableData("content", $total_fields[$key]);


	                        //we want to have entry at scorm data even if all values are null
	                        $fields_insert[$this_id]['content_ID']      = $this_id;


	                        if(!empty($organizations[$key])) {
	                            $organization_content_ID = $this_id;

	                            $fields_insert1 = array();
	                            $fields_insert1['content_ID']					= $this_id;
	                            $fields_insert1['lessons_ID']					= $lessons_ID;
	                            $fields_insert1['organization_ID']				= $organizations[$key]['id'];
	                            $fields_insert1['structure']					= $organizations[$key]['structure']?$organizations[$key]['structure']:'hierarchical';
	                            $fields_insert1['objectives_global_to_system']	= $organizations[$key]['objectives_global_to_system']?$organizations[$key]['objectives_global_to_system']:'true';
	                            $fields_insert1['shared_data_global_to_system']	= $organizations[$key]['shared_data_global_to_system']?$organizations[$key]['shared_data_global_to_system']:'true';
	                            eF_insertTableData("scorm_sequencing_organizations", $fields_insert1);
	                        }

	                        eF_insertTableData("scorm_sequencing_content_to_organization", array('lessons_ID'=>$lessons_ID, 'content_ID'=>$this_id, 'organization_content_ID'=> $organization_content_ID));

	                        $fields_insert1 = array();
	                        foreach ($rule_conditions[$key] as $key1 => $value1) {

	                            $fields_insert1['content_ID']				= $this_id;
	                            $fields_insert1['condition_combination']	= $value1['condition_combination']?$value1['condition_combination']:'all';
	                            $fields_insert1['rule_action']				= $value1['rule_action'];

	                            $scorm_sequencing_rules_ID = eF_insertTableData("scorm_sequencing_rules", $fields_insert1);

	                            $fields_insert2 = array();


	                            foreach ($rule_condition[$key1] as $key2 => $value2) {

	                                $fields_insert2['scorm_sequencing_rules_ID']	= $scorm_sequencing_rules_ID;
	                                $fields_insert2['referenced_objective']			= EfrontContentTreeSCORM :: form_id($value2['referenced_objective']);
	                                $fields_insert2['measure_threshold']			= $value2['measure_threshold'];
	                                $fields_insert2['operator']						= $value2['operator'];
	                                $fields_insert2['rule_condition']				= $value2['condition'];

	                                eF_insertTableData("scorm_sequencing_rule", $fields_insert2);
	                            }
	                        }

	                        $fields_insert1 = array();
	                        $primary_found = false; //to do

	                        foreach ($objective[$key] as $key1 => $value1) {
	                            $fields_insert1['content_ID']				= $this_id;
	                            $fields_insert1['objective_ID']				= EfrontContentTreeSCORM :: form_id($value1['objective_ID']);
	                            $fields_insert1['is_primary']				= $value1['is_primary'];
	                            $fields_insert1['satisfied_by_measure']		= $value1['satisfied_by_measure']?$value1['satisfied_by_measure']:'false';
	                            $fields_insert1['min_normalized_measure']	= $value1['min_normalized_measure']?$value1['min_normalized_measure']:'1.0';

	                            if($value1['is_primary']==1) {
	                                $primary_found = true;
	                            }

	                            $scorm_sequencing_objectives_ID = eF_insertTableData("scorm_sequencing_objectives", $fields_insert1);
	                        }

	                        //IMSSS:Each activity must have one, and only one, objective that contributes to rollup.
	                        $fields_insert1 = array();
	                        if(!$primary_found) {
	                            $fields_insert1['content_ID']   = $this_id;
	                            $fields_insert1['is_primary'] = '1';
	                            $fields_insert1['satisfied_by_measure'] = 'false';
	                            $fields_insert1['objective_ID'] = '';
	                            $fields_insert1['min_normalized_measure'] = '1';

	                            eF_insertTableData("scorm_sequencing_objectives", $fields_insert1);
	                        }

	                        $shared_objectives = array();
	                        $fields_insert1 = array();

	                        foreach ($map_info[$key] as $key1 => $value1) {
	                            $fields_insert1['content_ID']					= $this_id;
	                            $fields_insert1['objective_ID']					= EfrontContentTreeSCORM :: form_id($value1['objective_ID']);
	                            $fields_insert1['target_objective_ID']			= EfrontContentTreeSCORM :: form_id($value1['target_objective_ID']);
	                            $fields_insert1['read_satisfied_status']		= $value1['read_satisfied_status']?$value1['read_satisfied_status']:'true';
	                            $fields_insert1['read_normalized_measure']		= $value1['read_normalized_measure']?$value1['read_normalized_measure']:'true';
	                            $fields_insert1['write_satisfied_status']		= $value1['write_satisfied_status']?$value1['write_satisfied_status']:'false';
	                            $fields_insert1['write_normalized_measure']		= $value1['write_normalized_measure']?$value1['write_normalized_measure']:'false';

	                            $shared_objective[] = EfrontContentTreeSCORM :: form_id($value1['target_objective_ID']);
	                            eF_insertTableData("scorm_sequencing_map_info", $fields_insert1);
	                        }

	                        $fields_insert1 = array();
	                        foreach ($adl_seq_map_info[$key] as $key1 => $value1) {
	                            $fields_insert1['content_ID']				= $this_id;
	                            $fields_insert1['lessons_ID']				= $_SESSION['s_lessons_ID'];

	                            $fields_insert1['objective_ID']				= EfrontContentTreeSCORM :: form_id($value1['objective_ID']);
	                            $fields_insert1['target_objective_ID']		= EfrontContentTreeSCORM :: form_id($value1['target_objective_ID']);

	                            $fields_insert1['read_raw_score']			= $value1['read_raw_score']?$value1['read_raw_score']:'true';
	                            $fields_insert1['read_min_score']			= $value1['read_min_score']?$value1['read_min_score']:'true';
	                            $fields_insert1['read_max_score']			= $value1['read_max_score']?$value1['read_max_score']:'true';
	                            $fields_insert1['read_completion_status']	= $value1['read_completion_status']?$value1['read_completion_status']:'true';
	                            $fields_insert1['read_progress_measure']	= $value1['read_progress_measure']?$value1['read_progress_measure']:'true';

	                            $fields_insert1['write_raw_score']			= $value1['write_raw_score']?$value1['write_raw_score']:'false';
	                            $fields_insert1['write_min_score']			= $value1['write_min_score']?$value1['write_min_score']:'false';
	                            $fields_insert1['write_max_score']			= $value1['write_max_score']?$value1['write_max_score']:'false';
	                            $fields_insert1['write_completion_status']	= $value1['write_completion_status']?$value1['write_completion_status']:'false';
	                            $fields_insert1['write_progress_measure']	= $value1['write_progress_measure']?$value1['write_progress_measure']:'false';

	                            $shared_objective[] = EfrontContentTreeSCORM :: form_id($value1['target_objective_ID']);
	                            eF_insertTableData("scorm_sequencing_adlseq_map_info", $fields_insert1);
	                        }

	                        $fields_insert1 = array();
	                        $default_activity_flag = true;
	                        $default_objective_flag = true;

	                        foreach ($rollup_rules[$key] as $key1 => $value1) {
	                            $fields_insert1['content_ID']				= $this_id;
	                            $fields_insert1['child_activity_set']		= $value1['child_activity_set']?$value1['child_activity_set']:'all';
	                            $fields_insert1['minimum_count']			= $value1['minimum_count']?$value1['minimum_count']:'0';
	                            $fields_insert1['minimum_percent']			= $value1['minimum_percent']?$value1['minimum_percent']:'0.0000';
	                            $fields_insert1['condition_combination']	= $value1['condition_combination']?$value1['condition_combination']:'any';
	                            $fields_insert1['rule_action']				= $value1['rule_action'];

	                            $scorm_sequencing_rollup_rules_ID = eF_insertTableData("scorm_sequencing_rollup_rules", $fields_insert1);

	                            if(in_array($fields_insert1['rule_action'], array('completed', 'incomplete'))) {
	                                $default_activity_flag = false;
	                            }

	                            if(in_array($fields_insert1['rule_action'], array('satisfied', 'notSatisfied'))) {
	                                $default_objective_flag = false;
	                            }

	                            $fields_insert2 = array();



	                            foreach ($rollup_rule_conditions[$key1] as $key2 => $value2) {

	                                $fields_insert2['scorm_sequencing_rollup_rules_ID']		= $scorm_sequencing_rollup_rules_ID;
	                                $fields_insert2['operator']								= $value2['operator'];
	                                $fields_insert2['rule_condition']						= $value2['condition'];

	                                eF_insertTableData("scorm_sequencing_rollup_rule", $fields_insert2);
	                            }
	                        }

	                        $default_activity_flag = false;
	                        //Default activity rollup rules
	                        if($default_activity_flag) {

	                            $rollup_rules_satisfied = array('content_ID'=>$this_id, 'child_activity_set'=>'all', 'rule_action'=>'completed', 'minimum_count'=>'0', 'minimum_percent'=>'0', 'condition_combination'=>'any');
	                            $//rollup_rule_ID = eF_insertTableData("scorm_sequencing_rollup_rules", $rollup_rules_satisfied);

	                            $rollup_rule_satisfied = array('scorm_sequencing_rollup_rules_ID'=>$rollup_rule_ID, 'rule_condition'=>'completed');
	                            //eF_insertTableData("scorm_sequencing_rollup_rule", $rollup_rule_satisfied);

	                            $rollup_rules_not_satisfied = array('content_ID'=>$this_id, 'child_activity_set'=>'all', 'rule_action'=>'incomplete', 'minimum_count'=>'0', 'minimum_percent'=>'0', 'condition_combination'=>'any');
	                            //$rollup_rule_ID = eF_insertTableData("scorm_sequencing_rollup_rules", $rollup_rules_not_satisfied);

	                            $rollup_rule_not_satisfied = array('scorm_sequencing_rollup_rules_ID'=>$rollup_rule_ID, 'rule_condition'=>'activityProgressKnown');
	                            //eF_insertTableData("scorm_sequencing_rollup_rule", $rollup_rule_not_satisfied);
	                        }

	                        $default_objective_flag =false;
	                        //Default objective rollup rules
	                        if($default_objective_flag) {

	                            $rollup_rules_satisfied = array('content_ID'=>$this_id, 'child_activity_set'=>'all', 'rule_action'=>'satisfied', 'minimum_count'=>'0', 'minimum_percent'=>'0', 'condition_combination'=>'any');
	                            //$rollup_rule_ID = eF_insertTableData("scorm_sequencing_rollup_rules", $rollup_rules_satisfied);

	                            $rollup_rule_satisfied = array('scorm_sequencing_rollup_rules_ID'=>$rollup_rule_ID, 'rule_condition'=>'satisfied');
	                            //eF_insertTableData("scorm_sequencing_rollup_rule", $rollup_rule_satisfied);

	                            $rollup_rules_not_satisfied = array('content_ID'=>$this_id, 'child_activity_set'=>'all', 'rule_action'=>'notSatisfied', 'minimum_count'=>'0', 'minimum_percent'=>'0', 'condition_combination'=>'any');
	                            //$rollup_rule_ID = eF_insertTableData("scorm_sequencing_rollup_rules", $rollup_rules_not_satisfied);

	                            $rollup_rule_not_satisfied = array('scorm_sequencing_rollup_rules_ID'=>$rollup_rule_ID, 'rule_condition'=>'objectiveStatusKnown');
	                            //eF_insertTableData("scorm_sequencing_rollup_rule", $rollup_rule_not_satisfied);
	                        }


	                        //pr($constrained_choice[$key]);
	                        $fields_insert1 = array();
	                        if($constrained_choice[$key]) {
	                            $fields_insert1['content_ID']			= $this_id;
	                            $fields_insert1['prevent_activation']				= $constrained_choice[$key]['prevent_activation']?$constrained_choice[$key]['prevent_activation']:'false';
	                            $fields_insert1['constrain_choice']		= $constrained_choice[$key]['constrain_choice']?$constrained_choice[$key]['constrain_choice']:'false';

	                            eF_insertTableData("scorm_sequencing_constrained_choice", $fields_insert1);
	                        }


	                        if(empty($control_mode[$key])) {
	                            $control_mode[$key][0]['choice'] = 'true';
	                            $control_mode[$key][0]['choice_exit'] = 'true';
	                            $control_mode[$key][0]['flow'] = 'false';
	                            $control_mode[$key][0]['forward_only'] = 'false';
	                            $control_mode[$key][0]['use_current_attempt_objective_info'] = 'true';
	                            $control_mode[$key][0]['use_current_attempt_progress_info'] = 'true';
	                        }

	                        //echo $key;
	                        //pr($control_mode);
	                        $fields_insert1 = array();
	                        foreach ($control_mode[$key] as $key1 => $value1) {
	                            $fields_insert1['content_ID']							= $this_id;
	                            $fields_insert1['choice']								= $value1['choice']?$value1['choice']:'true';
	                            $fields_insert1['choice_exit']							= $value1['choice_exit']?$value1['choice_exit']:'true';
	                            $fields_insert1['flow']									= $value1['flow']?$value1['flow']:'false';
	                            $fields_insert1['forward_only']							= $value1['forward_only']?$value1['forward_only']:'false';
	                            $fields_insert1['use_current_attempt_objective_info']	= $value1['use_current_attempt_objective_info']?$value1['use_current_attempt_objective_info']:'true';
	                            $fields_insert1['use_current_attempt_progress_info']	= $value1['use_current_attempt_progress_info']?$value1['use_current_attempt_progress_info']:'true';

	                            eF_insertTableData("scorm_sequencing_control_mode", $fields_insert1);
	                        }

	                        if(empty($delivery_controls[$key])) {
	                            $delivery_controls[$key][0]['objective_set_by_content'] = 'false';
	                            $delivery_controls[$key][0]['completion_set_by_content'] = 'false';
	                            $delivery_controls[$key][0]['tracked'] = 'true';
	                        }

	                        $fields_insert1 = array();
	                        foreach ($delivery_controls[$key] as $key1 => $value1) {
	                            $fields_insert1['content_ID']					= $this_id;
	                            $fields_insert1['objective_set_by_content']		= $value1['objective_set_by_content']?$value1['objective_set_by_content']:'false';
	                            $fields_insert1['completion_set_by_content']	= $value1['completion_set_by_content']?$value1['completion_set_by_content']:'false';
	                            $fields_insert1['tracked']						= $value1['tracked']?$value1['tracked']:'true';

	                            eF_insertTableData("scorm_sequencing_delivery_controls", $fields_insert1);
	                        }

	                        $fields_insert1 = array();

	                        foreach ($maps[$key] as $key1 => $value1) {
	                            $fields_insert1['content_ID']					= $this_id;
	                            $fields_insert1['target_ID']					= $value1['target_ID'];
	                            $fields_insert1['read_shared_data']				= $value1['read_shared_data']?$value1['read_shared_data']:'true';
	                            $fields_insert1['write_shared_data']			= $value1['write_shared_data']?$value1['write_shared_data']:'true';

	                            eF_insertTableData("scorm_sequencing_maps", $fields_insert1);
	                        }


	                        $fields_insert1 = array();
	                        foreach ($limit_conditions[$key] as $key1=>$value1) {
	                            $fields_insert1['content_ID']						= $this_id;
	                            $fields_insert1['attempt_limit']					= $value1['attempt_limit'];
	                            $fields_insert1['attempt_absolute_duration_limit']	= $value1['attempt_absolute_duration_limit'];

	                            eF_insertTableData("scorm_sequencing_limit_conditions", $fields_insert1);
	                        }

	                        if(empty($completion_threshold[$key])) {
	                            $completion_threshold[$key][0]['completed_by_measure'] = 'false';
	                            $completion_threshold[$key][0]['min_progress_measure'] = '1.0';
	                            $completion_threshold[$key][0]['progress_weight'] = '1.0';
	                        }

	                        $fields_insert1 = array();
	                        foreach($completion_threshold[$key] as $key1=>$value1) {
	                            $fields_insert1['content_ID']			= $this_id;
	                            $fields_insert1['completed_by_measure'] = $value1['completed_by_measure']?$value1['completed_by_measure']:'false';
	                            $fields_insert1['min_progress_measure'] = $value1['min_progress_measure']?$value1['min_progress_measure']:'1.0';
	                            $fields_insert1['progress_weight']		= $value1['progress_weight']?$value1['progress_weight']:'1.0';

	                            eF_insertTableData("scorm_sequencing_completion_threshold", $fields_insert1);
	                        }

	                        if(empty($rollup_considerations[$key])) {
	                            $rollup_considerations[$key][0]['required_for_satisfied'] = 'always';
	                            $rollup_considerations[$key][0]['required_for_not_satisfied'] = 'always';
	                            $rollup_considerations[$key][0]['required_for_completed'] = 'always';
	                            $rollup_considerations[$key][0]['required_for_incomplete'] = 'always';
	                            $rollup_considerations[$key][0]['measure_satisfaction_if_active'] = 'true';

	                        }

	                        $fields_insert1 = array();
	                        foreach($rollup_considerations[$key] as $key1=>$value1) {
	                            $fields_insert1['content_ID']						= $this_id;
	                            $fields_insert1['required_for_satisfied']			= $value1['required_for_satisfied']?$value1['required_for_satisfied']:'always';
	                            $fields_insert1['required_for_not_satisfied']		= $value1['required_for_not_satisfied']?$value1['required_for_not_satisfied']:'always';
	                            $fields_insert1['required_for_completed']			= $value1['required_for_completed']?$value1['required_for_completed']:'always';
	                            $fields_insert1['required_for_incomplete']			= $value1['required_for_incomplete']?$value1['required_for_incomplete']:'always';
	                            $fields_insert1['measure_satisfaction_if_active']	= $value1['measure_satisfaction_if_active']?$value1['measure_satisfaction_if_active']:'true';

	                            eF_insertTableData("scorm_sequencing_rollup_considerations", $fields_insert1);
	                        }

	                        if(empty($rollup_controls[$key])) {
	                            $rollup_controls[$key][0]['rollup_objective_satisfied'] = 'true';
	                            $rollup_controls[$key][0]['rollup_objective_measure_weight'] = '1.0';
	                            $rollup_controls[$key][0]['rollup_progress_completion'] = 'true';
	                        }

	                        $fields_insert1 = array();
	                        foreach($rollup_controls[$key] as $key1=>$value1) {
	                            $fields_insert1['content_ID'] = $this_id;
	                            $fields_insert1['rollup_objective_satisfied']		= $value1['rollup_objective_satisfied']?$value1['rollup_objective_satisfied']:'true';
	                            $fields_insert1['rollup_objective_measure_weight']	= $value1['rollup_objective_measure_weight']?$value1['rollup_objective_measure_weight']:'1.0';
	                            $fields_insert1['rollup_progress_completion']		= $value1['rollup_progress_completion']?$value1['rollup_progress_completion']:'true';

	                            eF_insertTableData("scorm_sequencing_rollup_controls", $fields_insert1);
	                        }

	                        $fields_insert1 = array();
	                        foreach ($control_mode[$tagArray[$key]['parent_index']] as $key1=>$value1) {
	                            $hide_lms_ui[$key]['choice'] = $value1['choice'];
	                        }

	                        $fields_insert1[$key]['content_ID'] = $this_id;
	                        $fields_insert1[$key]['options']	= serialize($hide_lms_ui[$key]);

	                        eF_insertTableData("scorm_sequencing_hide_lms_ui", $fields_insert1[$key]);

	                        $tagArray[$key]['this_id'] = $this_id;
	                        foreach($tagArray[$key]['children'] as $key2 => $value2) {
	                            if (isset($total_fields[$value2])) {
	                                $total_fields[$value2]['parent_content_ID'] = $this_id;
	                            }
	                        }
	                    } else  {
	                        unset($total_fields[$key]);
	                    }
	                }

					/*
	                $fields_insert1 = array();
	                foreach (array_unique($shared_objective) as $key1=>$value1) {
	                    //$fields_insert1['lessons_ID'] = $_SESSION['s_lessons_ID'];
	                    $fields_insert1['content_ID'] = 0;
	                    $fields_insert1['is_primary'] = '1';
	                    $fields_insert1['satisfied_by_measure'] = 'false';
	                    $fields_insert1['objective_ID'] = EfrontContentTreeSCORM :: form_id($value1);
	                    $fields_insert1['min_normalized_measure'] = '1';


	                    eF_insertTableData("scorm_sequencing_objectives", $fields_insert1);
					}*/



	                //$directory = new EfrontDirectory(G_SCORMPATH);
	                //$directory -> copy(EfrontDirectory :: normalize($currentLesson -> getDirectory()).'/'.$scormFolderName, true);

	                //foreach ($files as $key => $value) {
	                //$newhref = $tagArray[$tagArray[$key]['parent_index']]['attributes']['XML:BASE'];
	                //copy(G_SCORMPATH."/".rtrim($newhref,"/")."/".rtrim($value,"/"), rtrim($currentLesson -> getDirectory(), "/")."/$this_id/".rtrim($newhref,"/")."/".rtrim($value,"/"));
	                //$this_id is put here so we can be sure that the files are put in a unique folder
	                //}

	                foreach ($timelimitaction as $key => $value) {
	                    $content_ID = $tagArray[$tagArray[$key]['parent_index']]['this_id'];

	                    $fields_insert[$content_ID]['content_ID']      = $content_ID;
	                    $fields_insert[$content_ID]['timelimitaction'] = $value;
	                }
	                foreach ($maxtimeallowed as $key => $value) {
	                    $content_ID = $tagArray[$tagArray[$key]['parent_index']]['this_id'];

	                    $fields_insert[$content_ID]['content_ID']     = $content_ID;
	                    $fields_insert[$content_ID]['maxtimeallowed'] = $value;
	                }
	                foreach ($masteryscore as $key => $value) {
	                    $content_ID = $tagArray[$tagArray[$key]['parent_index']]['this_id'];

	                    $fields_insert[$content_ID]['content_ID']   = $content_ID;
	                    $fields_insert[$content_ID]['masteryscore'] = $value;
	                }

	                foreach ($datafromlms as $key => $value) {
	                    $content_ID = $tagArray[$tagArray[$key]['parent_index']]['this_id'];

	                    $fields_insert[$content_ID]['content_ID']  = $content_ID;
	                    $fields_insert[$content_ID]['datafromlms'] = $value;
	                }


	                foreach ($fields_insert as $key => $value) {

	                    eF_insertTableData("scorm_data_2004", $value);
	                    if (isset($value['masteryscore']) && $value['masteryscore']) {
	                        eF_updateTableData("content", array("ctg_type" => "scorm_test"), "id=".$value['content_ID']);
	                    }
	                }

	                foreach ($prerequisites as $key => $value) {
	                    foreach ($tagArray as $key2 => $value2) {
	                        if (isset($value2['attributes']['IDENTIFIER']) && $value2['attributes']['IDENTIFIER'] == $value) {
	                            unset($fields_insert);
	                            $fields_insert['users_LOGIN'] = "*";
	                            $fields_insert['content_ID']  = $tagArray[$tagArray[$key]['parent_index']]['this_id'];
	                            $fields_insert['rule_type']   = "hasnot_seen";
	                            $fields_insert['rule_content_ID'] = $value2['this_id'];
	                            $fields_insert['rule_option'] = 0;
	                            eF_insertTableData("rules", $fields_insert);
	                        }
	                    }
	                }


	            }
	        } #cpp#endif
    	} #cpp#endif
        if (!$scorm2004) {

            foreach ($references as $key => $value) {
                //$ref = array_search($value, $resources);
                $ref = array_search($value['IDENTIFIERREF'], $resources);
                if ($ref !== false && !is_null($ref)) {
                    $data = file_get_contents($scormPath."/".$tagArray[$ref]['attributes']['HREF']);

                    $primitive_hrefs[$ref] = str_replace("\\", "/", $tagArray[$ref]['attributes']['HREF']);
                    $path_part[$ref]       = dirname($primitive_hrefs[$ref]);

                    foreach($tagArray[$ref]['children'] as $value2) {
                        if ($tagArray[$value2]['tag'] == 'DEPENDENCY') {
                            $idx = array_search($tagArray[$value2]['attributes']['IDENTIFIERREF'], $resources);

                            foreach ($tagArray[$idx]['children'] as $value3) {
                                if ($tagArray[$value3]['tag'] == 'FILE')  {
                                    $data = preg_replace("#(\.\.\/(\w+\/)*)?".$tagArray[$value3]['attributes']['HREF']."#", $currentLesson -> getDirectory()."/".$scormFolderName.'/'.$path_part[$ref]."/$1".$tagArray[$value3]['attributes']['HREF'], $data);
                                }
                            }
                        }
                    }
                    //$total_fields[$key]['data'] = eF_postProcess(str_replace("'","&#039;",$data));
                    if ($parameters['embed_type'] == 'iframe') {
                        //$total_fields[$key]['data'] = '<iframe height = "100%"  width = "100%" frameborder = "no" name = "scormFrameName" id = "scormFrameID" src = "'.$currentLesson -> getDirectoryUrl()."/".$scormFolderName.'/'.$primitive_hrefs[$ref].'" onload = "if (window.eF_js_setCorrectIframeSize) {eF_js_setCorrectIframeSize();} else {setIframeSize = true;}"></iframe>';
	                    $total_fields[$key]['data'] = '<iframe '.$parameters['iframe_parameters'].' name = "scormFrameName" id = "scormFrameID" src = "'.$currentLesson -> getDirectoryUrl()."/".$scormFolderName.'/'.$primitive_hrefs[$ref]. $value['PARAMETERS']. '" onload = "if (window.eF_js_setCorrectIframeSize) {eF_js_setCorrectIframeSize();} else {setIframeSize = true;}"></iframe>';
                    } else {
                        $total_fields[$key]['data'] = '
                            <div style = "text-align:center;height:300px">
                            	<span>##CLICKTOSTARTUNIT##</span><br/>
		                    	<input type = "button" value = "##STARTUNIT##" class = "flatButton" onclick = \'window.open("'.$currentLesson -> getDirectoryUrl()."/".urlencode($scormFolderName).'/'.urlencode($primitive_hrefs[$ref]). $value['PARAMETERS'].'", "scormFrameName", "'.$parameters['popup_parameters'].'")\' >
                        	</div>';
                    }
                }
            }
            
            $lastUnit = $currentContent -> getLastNode();
            $lastUnit ? $this_id  = $lastUnit['id'] : $this_id = 0;
            //$this_id = $tree[sizeof($tree) - 1]['id'];

            foreach ($total_fields as $key => $value)  {
                if (isset($value['ctg_type']))  {
                	$total_fields[$key]['previous_content_ID'] = $this_id;

                    if (!isset($total_fields[$key]['parent_content_ID'])) {
                        $total_fields[$key]['parent_content_ID'] = 0;
                    }

                    $total_fields[$key]['options'] = serialize(array('complete_unit_setting' => EfrontUnit::COMPLETION_OPTIONS_HIDECOMPLETEUNITICON));
                    $this_id = eF_insertTableData("content", $total_fields[$key]);
                    $tagArray[$key]['this_id'] = $this_id;
                    foreach($tagArray[$key]['children'] as $key2 => $value2) {
                        if (isset($total_fields[$value2])) {
                            $total_fields[$value2]['parent_content_ID'] = $this_id;
                        }
                    }
                } else  {
                    unset($total_fields[$key]);
                }
            }
            //$directory = new EfrontDirectory(G_SCORMPATH);

            //$directory -> copy(EfrontDirectory :: normalize($currentLesson -> getDirectory()).'/'.$scormFolderName, true);

            //foreach ($files as $key => $value) {
            //$newhref = $tagArray[$tagArray[$key]['parent_index']]['attributes']['XML:BASE'];
            //copy(G_SCORMPATH."/".rtrim($newhref,"/")."/".rtrim($value,"/"), rtrim($currentLesson -> getDirectory(), "/")."/$this_id/".rtrim($newhref,"/")."/".rtrim($value,"/"));    //$this_id is put here so we can be sure that the files are put in a unique folder
            //}

            foreach ($timelimitaction as $key => $value) {
                $content_ID = $tagArray[$tagArray[$key]['parent_index']]['this_id'];

                $fields_insert[$content_ID]['content_ID']      = $content_ID;
                $fields_insert[$content_ID]['timelimitaction'] = $value;
            }
            foreach ($maxtimeallowed as $key => $value) {
                $content_ID = $tagArray[$tagArray[$key]['parent_index']]['this_id'];

                $fields_insert[$content_ID]['content_ID']     = $content_ID;
                $fields_insert[$content_ID]['maxtimeallowed'] = $value;
            }
            foreach ($masteryscore as $key => $value) {
                $content_ID = $tagArray[$tagArray[$key]['parent_index']]['this_id'];

                $fields_insert[$content_ID]['content_ID']   = $content_ID;
                $fields_insert[$content_ID]['masteryscore'] = $value;
            }
            foreach ($datafromlms as $key => $value) {
                $content_ID = $tagArray[$tagArray[$key]['parent_index']]['this_id'];

                $fields_insert[$content_ID]['content_ID']  = $content_ID;
                $fields_insert[$content_ID]['datafromlms'] = $value;
            }

            foreach ($fields_insert as $key => $value) {
                eF_insertTableData("scorm_data", $value);
                if (isset($value['masteryscore']) && $value['masteryscore']) {
                    eF_updateTableData("content", array("ctg_type" => "scorm_test"), "id=".$value['content_ID']);
                }
            }

            foreach ($prerequisites as $key => $parts) {
            	foreach (explode("&", $parts) as $value) {
            		foreach ($tagArray as $key2 => $value2) {
            			if (isset($value2['attributes']['IDENTIFIERREF']) && $value2['attributes']['IDENTIFIERREF'] == $value) {
            				//pr($value2);
            				unset($fields_insert);
            				$fields_insert['users_LOGIN'] = "*";
            				$fields_insert['content_ID']  = $tagArray[$tagArray[$key]['parent_index']]['this_id'];
            				$fields_insert['rule_type']   = "hasnot_seen";
            				$fields_insert['rule_content_ID'] = $value2['this_id'];
            				$fields_insert['rule_option'] = 0;
            				eF_insertTableData("rules", $fields_insert);
            			}
            		}
            	}
            }
        }
        //exit;

        EfrontCache::getInstance()->deleteCache("content_tree:{$lesson->lesson['id']}");
    }


    /**
     * This function analyzes the manifest. In order to perform faster, manifest entities are analyzed one-by-one (on the fly)
     * during parsing and not stored into memory
     * Algorithm:
     * 1. Detect organization elements. Each one will consist a separate units structure inside efront
     * 2. Dive inside an organization and detect item elements. Each <item> corresponds to a "unit" in efront
     *
     *
     *
     *
     * @param $manifest
     * @return unknown_type
     */

    public static function import2($lesson, $manifest) {
        //@todo: parse $lesson

        //foreach ($namespaces as $prefix => $ns) {
        //$xml->registerXPathNamespace($prefix, $ns);
        //}
        //pr($xml -> xpath("$dfn:organizations/$dfn:organization/$dfn:item/adlcp:timeLimitAction"));

        $xml = simplexml_load_file(G_SCORMPATH.'imsmanifest.xml', 'SimpleXMLIterator');

        $namespaces = $xml -> getNamespaces(true);
        if (isset($namespaces[""])) {									//See notes for xpath() in php.net site
            $dfn = "default";
            $xml -> registerXPathNamespace($dfn, $namespaces[""]);  	// register a prefix for that default namespace:
            //$xml -> organizations -> registerXPathNamespace($dfn, $namespaces[""]);  	// register a prefix for that default namespace:
        }

        /**
         * Manifest (1/1): may contain the following elements:
         * - metadata (1/1)
         * - organizations (1/1)
         * - resources (1/1)
         * - manifest
         * - imsss:sequencingCollection (0/1)
         * And the following attributes
         * - identifier (xs:ID, m): unique identifier
         * - version (xs:string, o): manifest version
         * - xml:base (xs:anyURI, o): provides a relative path offset for the content file(s) contained in the manifest
         */
        $manifest['identifier'] = (string)$xml -> attributes() -> identifier;
        $manifest['version']	= (string)$xml -> attributes() -> version;
        //@todo: handle 'xml:base'
        //$manifest['xml:base']	= (string)$xml -> attributes() -> xml:base;

        /**
         * Metadata: may contain the following elements:
         * - schema (1/1)
         * - schemaversion (1/1)
         * - {metadata} (0/1)
         */
        $metadata['schema'] 	   = (string)$xml -> metadata -> schema;
        $metadata['schemaversion'] = (string)$xml -> metadata -> schemaversion;
        //@todo: handle metadata

        /*
         * Organizations: may contain the following elements:
         * - organization (1/M)
         * And the following attributes:
         * - default (xs:IDREF, m): The id of the default organization
         */
        $organizations['default'] = (string)$xml -> organizations -> attributes();
        //@todo: check that default is actually an existing organization

        /*
         * Organization: may contain the following elements:
         * - title (1/1)
         * - item (1/M)
         * - metadata (0/1)
         * - imsss:sequencing
         * And the following attributes:
         * - identifier (xs:ID, m): identifier (unique within the manifest)
         * - structure (xs:string, o): Describes the shape of the organization (default: hierarchical)
         * - adlseq:objectivesGlobalToSystem (xs:boolean, o): self-explanatory ;)
         */
        foreach ($xml -> organizations -> organization as $org) {
            $org -> registerXPathNamespace($dfn, $namespaces[""]);  	// register a prefix for that default namespace:

            $id = (string)$org -> attributes() -> identifier;
            $org -> attributes() -> structure ? $organization[$id]['structure'] = $org -> attributes() -> structure :$organization[$id]['structure'] = 'hierarchical';
            $organization[$id]['title'] = $org -> attributes() -> title;

            //@todo: the importing may be done below existing elements, take this into account when considering $previousContentId (its initial value may not be 0)
            $contentTree 	 = new EfrontContentTree($lesson);
            $previousContent = $contentTree -> getLastNode() or $previousContent = array('id' => 0);
            //Create the "holding" unit, an empty unit that will hold this organization's elements
            $previousContent = $parentContent = EfrontUnit::createUnit(array('name' 			   => $organization[$id]['title'],
																		     'parent_content_ID'   => 0,
																			 'previous_content_ID' => $previousContent['id'],
																			 'lessons_ID'		   => $lesson));

            //Get contents of the organization
            foreach($org as $key => $value) {

                /*
                 * Item: may contain the following elements:
                 * - title (1/1)
                 * - item 0/M
                 * - metadata 0/1
                 * - adlcp: timeLimitAction 0/1
                 * - adlcp: dataFromLMS 0/1
                 * - adlcp: completionThreshold 0/1
                 * - imsss:sequencing
                 * - adlnav:presentation
                 * And the following attributes:
                 * - identifier (xs:ID, m): a unique identifier
                 * - identifierref (xs:string, o): a reference to a resource
                 * - isvisible (xs:boolean, o): whether this item is displayed when the structure of the package is displayed or rendered (Default true)
                 * - parameters (xs:string, o): static parameters to be passed to the resource at launch time (max 1000 chars)
                 */
                if ($key == 'item') {
                    $itemId = (string)$value -> attributes() -> identifier;
                    //pr($value -> attributes() -> identifier);
                    $item = array('identifier'    => $itemId,
								  'identifierref' => (string)$value -> attributes() -> identifierref,
								  'isvisible' 	  => (string)$value -> attributes() -> isvisible,
								  'parameters'    => (string)$value -> attributes() -> parameters,
								  'title'		  => (string)$value -> title,
							      'timeLimitAction' 	=> (string)reset($org -> xpath("$dfn:item[@identifier='$itemId']/adlcp:timeLimitAction")),	//reset() returns the first element of an array, handy because xpath() returns array
								  'dataFromLMS'   		=> (string)reset($org -> xpath("$dfn:item[@identifier='$itemId']/adlcp:dataFromLMS")),
								  'completionThreshold' => (string)reset($org -> xpath("$dfn:item[@identifier='$itemId']/adlcp:completionThreshold")));
                    //@todo:<imsss:sequencing>, <adlnav:presentation>
                    //@todo: nested items
                    //@todo: metadata



                    $previousContent = EfrontUnit::createUnit(array('name' 			      => $item['title'],
																	'parent_content_ID'   => $parentContent['id'],
																	'previous_content_ID' => $previousContent['id'],
																	'lessons_ID'		  => $lesson));
                    $items[$itemId] = $item['identifierref'];
                }
            }
            //@todo: handle adlseq:objectivesGlobalToSystem
        }

        /*
         * Resources: may contain the following elements:
         * - resource (0/M)
         * And the following attributes:
         * - xml:base (xs:anyURI, o): provides a relative path offset for the content file(s)
         */
        $resources = $xml -> resources;
        $resources -> registerXPathNamespace($dfn, $namespaces[""]);  	// register a prefix for that default namespace:

        /*
         * Resource: may contain the following elements:
         * - metadata (0/1)
         * - file (0/M)
         * - dependency (0/M)
         * And the following attributes:
         * - identifier (xs:ID, m): a unique identifier
         * - type (xs:string, m): the type of the resource
         * - href (xs:string, o): the �entry point� or �launching point� of this resource
         * - xml:base (xs:anyURI, o): a relative path offset for the files contained in the manifest
         * - adlcp:scormType (xs:string, m): the type of SCORM resource ("sco" or "asset")
         */
        foreach ($resources -> resource as $key => $value) {
            $resourceId = (string)$value -> attributes() -> identifier;

            $resource = array('identifier' => $resourceId,
			                  'type' 	   => (string)$value -> attributes() -> type,
							  'href'	   => (string)$value -> attributes() -> href,
							  'base'	   => (string)$value -> attributes($namespaces['xml']) -> base,
							  'scormType'  => (string)$value -> attributes($namespaces['adlcp']) -> scormType);

            /**
             * File: may contain the following elements:
             * - metadata (0/1)
             * And the following attributes:
             * - href (xs:string, m): identifies the location of the file
             */
            foreach($value -> file as $f) {
                $file = array('href' => (string)$f -> attributes() -> href);
            }

            /**
             * Dependency: may contain the following elements:
             * <none>
             * And the following attributes:
             * - identifierref (xs:string, m): an identifier attribute of a resource
             */
            foreach($value -> dependency as $d) {
                $dependency = array('identifierref' => (string)$d -> attributes() -> identifierref);
            }

        }

        //@todo: sequencingCollection



        //pr($organization);

        //    	$result = $xml -> xpath("//$dfn:manifest/$dfn:organizations/$dfn:organization");

        /*
         $iterator = new SimpleXMLIterator($xml -> asXML());
         foreach (new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST) as $key => $value) {

         }
         */

        /*
         //$iterator = new SimpleXMLIterator($data);
         //$iterator = simplexml_load_string($data, 'SimpleXMLIterator');

         */
    }


    public static function createUnitFromItem($item) {
        $fields = array('name'                => $item['name'],
                        'data'                => '',
                        'parent_content_ID'   => '',
                        'lessons_ID'          => '',
                        'ctg_type'            => 'scorm',
                        'previous_content_ID' => '');

        //pr($item);
    }
}

