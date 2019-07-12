<?xml version="1.0" encoding="UTF-8"?>
<!--
	xmlns="http://www.w3.org/2001/10/synthesis" 
	-->
<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
	 
	xsi:schemaLocation="http://www.w3.org/2001/10/synthesis	http://www.w3.org/TR/speech-synthesis11/synthesis.xsd" 
	xmlns:xs="http://www.w3.org/2001/XMLSchema" 
	xmlns:func="http://exslt.org/functions" 
	extension-element-prefixes="func php str" 
	xmlns:functx="http://www.functx.com"
	xmlns:php="http://php.net/xsl" xmlns:str="http://exslt.org/strings"
	xmlns:axf="http://www.antennahouse.com/names/XSL/Extensions">
	
	<xsl:import href="terms.xsl"></xsl:import>	
	
	<xsl:output method="xml" version="1.0" encoding="UTF-8"	indent="yes"></xsl:output>
	
	<xsl:variable name="lang">
		<xsl:value-of select="/article/meta/lang"></xsl:value-of>
	</xsl:variable>	

	<xsl:template match="loop">
		<xsl:call-template name="contentpages"></xsl:call-template>
	</xsl:template>
	
	<xsl:template name="contentpages">
		<xsl:apply-templates select="article"/>
	</xsl:template>	
	
	<xsl:template match="article">
		<xsl:element name="article">
			<xsl:attribute name="id">
				<xsl:value-of select="@id"></xsl:value-of>
			</xsl:attribute>
			<xsl:element name="speak">
				<xsl:attribute name="voice">
					<xsl:text>2</xsl:text>
				</xsl:attribute>
			
				<xsl:element name="p">
					<xsl:choose>
						<xsl:when test="@tocnumber=''">
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="$word_chapter"/>
							<xsl:text> </xsl:text>
							<xsl:value-of select="@tocnumber"></xsl:value-of>
						</xsl:otherwise>
					</xsl:choose>
					
					<xsl:element name="break">
						<xsl:attribute name="strength">
							<xsl:text>medium</xsl:text>
						</xsl:attribute>
					</xsl:element>

					<xsl:if test="@toctext">
						<xsl:value-of select="@toctext"></xsl:value-of>
					</xsl:if>
					<xsl:if test="@title">
						<xsl:value-of select="@title"></xsl:value-of>
					</xsl:if>
					
					<xsl:element name="break">
						<xsl:attribute name="time">
							<xsl:text>700ms</xsl:text>
						</xsl:attribute>
					</xsl:element>

				</xsl:element>
						
			</xsl:element>

			<xsl:apply-templates/>
			
		</xsl:element>
	
	</xsl:template>	


	<xsl:template match="loop_objects">
	</xsl:template>
	
	<xsl:template match="link">
	</xsl:template>
	<xsl:template match="php_link">
	</xsl:template>
	<xsl:template match="php_link_image">
	</xsl:template>

	<xsl:template match="extension">
	
		<xsl:choose>
			<xsl:when test="@extension_name='loop_figure'">
				<xsl:call-template name="loop_object">
                	<xsl:with-param name="object" select="."></xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="@extension_name='loop_formula'">
				<xsl:call-template name="loop_object">
                	<xsl:with-param name="object" select="."></xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="@extension_name='loop_listing'">
				<xsl:call-template name="loop_object">
                	<xsl:with-param name="object" select="."></xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="@extension_name='loop_media'">
				<xsl:call-template name="loop_object">
                	<xsl:with-param name="object" select="."></xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="@extension_name='loop_table'">
				<xsl:call-template name="loop_object">
                	<xsl:with-param name="object" select="."></xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="@extension_name='loop_task'">
				<xsl:call-template name="loop_object">
                	<xsl:with-param name="object" select="."></xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			<xsl:when test="@extension_name='loop_reference'">
				<xsl:call-template name="loop_reference">
                	<xsl:with-param name="object" select="."></xsl:with-param>
				</xsl:call-template>
			</xsl:when>

			<xsl:when test="@extension_name='loop_area'">
				<xsl:call-template name="loop_area"> </xsl:call-template>
			</xsl:when>

			<xsl:when test="@extension_name='math'">
				<xsl:call-template name="math">
                	<xsl:with-param name="object">
						<xsl:copy-of select="php:function('xsl_transform_math_ssml', .)"></xsl:copy-of>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:when>

			<xsl:when test="@extension_name='loop_title'">
				<xsl:apply-templates/>
			</xsl:when>	
			<xsl:when test="@extension_name='loop_description'">
				<xsl:apply-templates/>		
			</xsl:when>	
			<xsl:when test="@extension_name='loop_copyright'">
				<xsl:apply-templates/>
			</xsl:when>	

			<xsl:when test="@extension_name='syntaxhighlight'">
				<xsl:call-template name="syntaxhighlight"></xsl:call-template>
			</xsl:when>	

			<xsl:when test="@extension_name='loop_spoiler'">
				<xsl:call-template name="loop_spoiler">
                	<xsl:with-param name="object">
						<xsl:copy-of select="."></xsl:copy-of>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:when>	

			<xsl:when test="@extension_name='spoiler'">
				<xsl:call-template name="loop_spoiler">
                	<xsl:with-param name="object">
						<xsl:copy-of select="."></xsl:copy-of>
					</xsl:with-param>
				</xsl:call-template>
			</xsl:when>	

			
		</xsl:choose>	
		<!--
				<xsl:element name="break">
					<xsl:attribute name="strength">
						<xsl:text>medium</xsl:text>
					</xsl:attribute>
				</xsl:element>
		-->
	</xsl:template>
	
	
	<xsl:template name="loop_area">

		<xsl:variable name="looparea_type">
		<xsl:choose> <!-- todo: trying to find a way to do this a much shorter way -->
			<xsl:when test="@type='task'"><xsl:value-of select="$word_looparea_task"></xsl:value-of></xsl:when>
			<xsl:when test="@type='timerequirement'"><xsl:value-of select="$word_looparea_timerequirement"></xsl:value-of></xsl:when>
			<xsl:when test="@type='learningobjectives'"><xsl:value-of select="$word_looparea_learningobjectives"></xsl:value-of></xsl:when>
			<xsl:when test="@type='arrangement'"><xsl:value-of select="$word_looparea_arrangement"></xsl:value-of></xsl:when>
			<xsl:when test="@type='example'"><xsl:value-of select="$word_looparea_example"></xsl:value-of></xsl:when>
			<xsl:when test="@type='reflection'"><xsl:value-of select="$word_looparea_reflection"></xsl:value-of></xsl:when>
			<xsl:when test="@type='notice'"><xsl:value-of select="$word_looparea_notice"></xsl:value-of></xsl:when>
			<xsl:when test="@type='important'"><xsl:value-of select="$word_looparea_important"></xsl:value-of></xsl:when>
			<xsl:when test="@type='annotation'"><xsl:value-of select="$word_looparea_annotation"></xsl:value-of></xsl:when>
			<xsl:when test="@type='definition'"><xsl:value-of select="$word_looparea_definition"></xsl:value-of></xsl:when>
			<xsl:when test="@type='formula'"><xsl:value-of select="$word_looparea_formula"></xsl:value-of></xsl:when>
			<xsl:when test="@type='markedsentence'"><xsl:value-of select="$word_looparea_markedsentence"></xsl:value-of></xsl:when>
			<xsl:when test="@type='sourcecode'"><xsl:value-of select="$word_looparea_sourcecode"></xsl:value-of></xsl:when>
			<xsl:when test="@type='summary'"><xsl:value-of select="$word_looparea_summary"></xsl:value-of></xsl:when>
			<xsl:when test="@type='indentation'"><xsl:value-of select="$word_looparea_indentation"></xsl:value-of></xsl:when>
			<xsl:when test="@type='norm'"><xsl:value-of select="$word_looparea_norm"></xsl:value-of></xsl:when>
			<xsl:when test="@type='law'"><xsl:value-of select="$word_looparea_law"></xsl:value-of></xsl:when>
			<xsl:when test="@type='question'"><xsl:value-of select="$word_looparea_question"></xsl:value-of></xsl:when>
			<xsl:when test="@type='practice'"><xsl:value-of select="$word_looparea_practice"></xsl:value-of></xsl:when>
			<xsl:when test="@type='exercise'"><xsl:value-of select="$word_looparea_exercise"></xsl:value-of></xsl:when>
			<xsl:when test="@type='websource'"><xsl:value-of select="$word_looparea_websource"></xsl:value-of></xsl:when>
			<xsl:when test="@type='experiment'"><xsl:value-of select="$word_looparea_experiment"></xsl:value-of></xsl:when>
			<xsl:when test="@type='citation'"><xsl:value-of select="$word_looparea_citation"></xsl:value-of></xsl:when>
			<xsl:when test="@type='citation'"><xsl:value-of select="$word_looparea_citation"></xsl:value-of></xsl:when>
			<xsl:otherwise>
				<xsl:if test="@icontext"><xsl:value-of select="@icontext"></xsl:value-of></xsl:if>
			</xsl:otherwise> <!-- todo: error msg? -->
		</xsl:choose>
	</xsl:variable>
<!-- 			
	<xsl:value-of select="$phrase_looparea_start"></xsl:value-of>
	<xsl:text> </xsl:text> -->
	<xsl:element name="break">
		<xsl:attribute name="strength">
			<xsl:text>strong</xsl:text>
		</xsl:attribute>
	</xsl:element>
	<xsl:value-of select="$looparea_type"></xsl:value-of>
	<xsl:element name="break">
		<xsl:attribute name="strength">
			<xsl:text>strong</xsl:text>
		</xsl:attribute>
	</xsl:element>
	<xsl:apply-templates/>
	<xsl:element name="break">
		<xsl:attribute name="strength">
			<xsl:text>strong</xsl:text>
		</xsl:attribute>
	</xsl:element>
	<!-- <xsl:value-of select="$phrase_looparea_end"></xsl:value-of>
	<xsl:text> </xsl:text>
	<xsl:value-of select="$looparea_type"></xsl:value-of>
	<xsl:element name="break">
		<xsl:attribute name="strength">
			<xsl:text>strong</xsl:text>
		</xsl:attribute>
	</xsl:element> -->
	</xsl:template>

	<xsl:template name="math">
		<xsl:param name="object"></xsl:param>
		<xsl:text> </xsl:text>
			<xsl:element name="break">
				<xsl:attribute name="strength">
					<xsl:text>medium</xsl:text>
				</xsl:attribute>
			</xsl:element>

			<xsl:element name="lang">
                <xsl:attribute name="xml:lang">
					<xsl:text>en-GB</xsl:text><!-- todo add language support in mathoid -->
				</xsl:attribute>
				<xsl:value-of select="$object"></xsl:value-of>
			</xsl:element>

			<xsl:element name="break">
				<xsl:attribute name="strength">
					<xsl:text>medium</xsl:text>
				</xsl:attribute>
			</xsl:element>

		<xsl:text> </xsl:text>

	</xsl:template>

	<xsl:template name="syntaxhighlight">
		<xsl:value-of select="$phrase_syntaxhighlight"></xsl:value-of>
			<xsl:element name="break">
				<xsl:attribute name="strength">
					<xsl:text>medium</xsl:text>
				</xsl:attribute>
			</xsl:element>
	</xsl:template>
	
	<xsl:template name="loop_spoiler">
		<xsl:param name="object"></xsl:param>

		<xsl:element name="break">
			<xsl:attribute name="strength">
				<xsl:text>medium</xsl:text>
			</xsl:attribute>
		</xsl:element>
		
		<xsl:choose>
			<xsl:when test="./descendant::extension[@extension_name='loop_spoiler_text']">
				<xsl:apply-templates select="./descendant::extension[@extension_name='loop_spoiler_text']" mode="loop_object"></xsl:apply-templates>
			</xsl:when>
			<xsl:when test="@text">
				<xsl:value-of select="@text"></xsl:value-of>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$word_spoiler_defaulttitle"></xsl:value-of>
			</xsl:otherwise>
		</xsl:choose>	

		<xsl:element name="break">
			<xsl:attribute name="strength">
				<xsl:text>strong</xsl:text>
			</xsl:attribute>
		</xsl:element>

		<xsl:text> </xsl:text>
		<xsl:value-of select="$phrase_spoiler_start"></xsl:value-of>
		<xsl:element name="break">
			<xsl:attribute name="strength">
				<xsl:text>strong</xsl:text>
			</xsl:attribute>
		</xsl:element>
		<xsl:text> </xsl:text>
		<xsl:apply-templates/>
		<xsl:element name="break">
			<xsl:attribute name="strength">
				<xsl:text>strong</xsl:text>
			</xsl:attribute>
		</xsl:element>
		<xsl:value-of select="$phrase_spoiler_end"></xsl:value-of>
		<xsl:text> </xsl:text>
		<xsl:element name="break">
			<xsl:attribute name="strength">
				<xsl:text>strong</xsl:text>
			</xsl:attribute>
		</xsl:element>
	</xsl:template>


	<xsl:template name="loop_reference">
		<xsl:param name="object"></xsl:param>

		<xsl:variable name="objectid">
			<xsl:choose>
				<xsl:when test="@id"> 
					<xsl:value-of select="@id"></xsl:value-of>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text></xsl:text>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
			
			<xsl:choose>
				<xsl:when test="$object=''">
					<xsl:choose>
						<xsl:when test="//*/loop_object[@refid = $objectid]/@object_type='loop_figure'">
							<xsl:value-of select="$word_figure"></xsl:value-of>
						</xsl:when>
						<xsl:when test="//*/loop_object[@refid = $objectid]/@object_type='loop_formula'">
							<xsl:value-of select="$word_formula"></xsl:value-of>
						</xsl:when>
						<xsl:when test="//*/loop_object[@refid = $objectid]/@object_type='loop_listing'">
							<xsl:value-of select="$word_listing"></xsl:value-of>
						</xsl:when>
						<xsl:when test="//*/loop_object[@refid = $objectid]/@object_type='loop_media'">
							<xsl:value-of select="$word_media"></xsl:value-of>
						</xsl:when>
						<xsl:when test="//*/loop_object[@refid = $objectid]/@object_type='loop_task'">
							<xsl:value-of select="$word_task"></xsl:value-of>
						</xsl:when>
						<xsl:when test="//*/loop_object[@refid = $objectid]/@object_type='loop_table'">
							<xsl:value-of select="$word_table"></xsl:value-of>
						</xsl:when>
						<xsl:otherwise>
						</xsl:otherwise>
					</xsl:choose>
					<xsl:text> </xsl:text>
					<xsl:if test="//*/loop_object[@refid = $objectid]/object_number">
						<xsl:value-of select="//*/loop_object[@refid = $objectid]/object_number"></xsl:value-of>
					</xsl:if>

					<xsl:if test="$object/@title='true'">
						<xsl:text>: </xsl:text>
						<xsl:if test="//*/loop_object[@refid = $objectid]/object_title">
							<xsl:value-of select="//*/loop_object[@refid = $objectid]/object_title"></xsl:value-of>
						</xsl:if>
					</xsl:if>

				</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates/>
				</xsl:otherwise>
			</xsl:choose>

	</xsl:template>

	<xsl:template name="loop_object">
		<xsl:param name="object"></xsl:param>

		<xsl:variable name="objectid">
			<xsl:choose>
				<xsl:when test="$object[@index='false']"> 
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="@id"></xsl:value-of>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<xsl:element name="p">
			<xsl:text> </xsl:text>
		</xsl:element>
		<xsl:element name="break">
			<xsl:attribute name="time">
				<xsl:text>700ms</xsl:text>
			</xsl:attribute>
		</xsl:element>
		<xsl:choose>
			<xsl:when test="//*/loop_object[@refid = $objectid]/object_number">
			
				<xsl:element name="p">
					<xsl:choose>
						<xsl:when test="$object[@extension_name='loop_figure']">
							<xsl:value-of select="$phrase_figure_number"></xsl:value-of>
						</xsl:when>
						<xsl:when test="$object[@extension_name='loop_formula']">
							<xsl:value-of select="$phrase_formula_number"></xsl:value-of>
						</xsl:when>
						<xsl:when test="$object[@extension_name='loop_listing']">
							<xsl:value-of select="$phrase_listing_number"></xsl:value-of>
						</xsl:when>
						<xsl:when test="$object[@extension_name='loop_media']">
							<xsl:value-of select="$phrase_media_number"></xsl:value-of>
						</xsl:when>
						<xsl:when test="$object[@extension_name='loop_task']">
							<xsl:value-of select="$phrase_task_number"></xsl:value-of>
						</xsl:when>
						<xsl:when test="$object[@extension_name='loop_table']">
							<xsl:value-of select="$phrase_table_number"></xsl:value-of>
						</xsl:when>
						<xsl:otherwise>
						</xsl:otherwise>
					</xsl:choose>
					<xsl:value-of select="//*/loop_object[@refid = $objectid]/object_number"></xsl:value-of>
				</xsl:element>
				<xsl:text> </xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="$object[@extension_name='loop_figure']">
						<xsl:value-of select="$phrase_figure"></xsl:value-of>
					</xsl:when>
					<xsl:when test="$object[@extension_name='loop_formula']">
						<xsl:value-of select="$phrase_formula"></xsl:value-of>
					</xsl:when>
					<xsl:when test="$object[@extension_name='loop_listing']">
						<xsl:value-of select="$phrase_listing"></xsl:value-of>
					</xsl:when>
					<xsl:when test="$object[@extension_name='loop_media']">
						<xsl:value-of select="$phrase_media"></xsl:value-of>
					</xsl:when>
					<xsl:when test="$object[@extension_name='loop_task']">
						<xsl:value-of select="$phrase_task"></xsl:value-of>
					</xsl:when>
					<xsl:when test="$object[@extension_name='loop_table']">
						<xsl:value-of select="$phrase_table"></xsl:value-of>
					</xsl:when>
					<xsl:otherwise>
					</xsl:otherwise>
				</xsl:choose>
				<xsl:text> </xsl:text>
			</xsl:otherwise>
		</xsl:choose>	


		<xsl:choose>
			<xsl:when test="$object/descendant::extension[@extension_name='loop_title']">
				<xsl:apply-templates select="$object/descendant::extension[@extension_name='loop_title']" mode="loop_object"></xsl:apply-templates>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$object/@title"></xsl:value-of>	
			</xsl:otherwise>
		</xsl:choose>
		<xsl:if test="($object/@description) or ($object/descendant::extension[@extension_name='loop_description'])">
			<xsl:element name="break">
				<xsl:attribute name="strength">
					<xsl:text>medium</xsl:text>
				</xsl:attribute>
			</xsl:element>
			<xsl:choose>
				<xsl:when test="$object/descendant::extension[@extension_name='loop_description']">
					<xsl:apply-templates select="$object/descendant::extension[@extension_name='loop_description']" mode="loop_object"></xsl:apply-templates>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$object/@description"></xsl:value-of>	
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
		<xsl:choose>
			<xsl:when test="$object/descendant::extension[@extension_name='loop_copyright']">
				<xsl:element name="break">
					<xsl:attribute name="strength">
						<xsl:text>medium</xsl:text>
					</xsl:attribute>
				</xsl:element>
				<xsl:apply-templates select="$object/descendant::extension[@extension_name='loop_copyright']" mode="loop_object"></xsl:apply-templates>
			</xsl:when>
			<xsl:otherwise>
				<xsl:element name="break">
					<xsl:attribute name="strength">
						<xsl:text>medium</xsl:text>
					</xsl:attribute>
				</xsl:element>
				<xsl:value-of select="$object/@copyright"></xsl:value-of>	
			</xsl:otherwise>
		</xsl:choose>

		<xsl:element name="break">
			<xsl:attribute name="strength">
				<xsl:text>medium</xsl:text>
			</xsl:attribute>
		</xsl:element>
	</xsl:template>

	<xsl:template match="heading">
	
		<xsl:element name="speak">
			<xsl:attribute name="voice">
				<xsl:text>2</xsl:text>
			</xsl:attribute>
			<!--<xsl:element name="amazon:autobreaths">-->
			<xsl:apply-templates/>
			<!--</xsl:element>-->
		</xsl:element>
		
		<xsl:element name="break">
			<xsl:attribute name="time">
				<xsl:text>1200ms</xsl:text>
			</xsl:attribute>
		</xsl:element>
		
	</xsl:template>
	
	
	<xsl:template match="paragraph">
		<xsl:choose>
			<xsl:when test="ancestor::paragraph">
				<xsl:element name="replace_speak">
					<xsl:attribute name="voice">
						<xsl:value-of select="functx:select_voice()"/>
					</xsl:attribute>
					<xsl:apply-templates/>
				</xsl:element>
				<xsl:element name="replace_speak_next">
					<xsl:attribute name="voice">
						<xsl:value-of select="functx:select_voice()"/>
					</xsl:attribute>
				</xsl:element>
			</xsl:when>
			<xsl:otherwise>
				<xsl:element name="speak">
					<xsl:attribute name="voice">
						<xsl:value-of select="functx:select_voice()"/>
					</xsl:attribute>
					<xsl:apply-templates/>
					
					<xsl:element name="break">
						<xsl:attribute name="strength">
							<xsl:text>strong</xsl:text>
						</xsl:attribute>
					</xsl:element>

				</xsl:element>
				

			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<func:function name="functx:select_voice">
	
		<xsl:choose>
			<xsl:when test="extension[@extension_name='loop_figure']">
        		<func:result>2</func:result>
			</xsl:when>
			<xsl:when test="extension[@extension_name='loop_formula']">
        		<func:result>2</func:result>
			</xsl:when>
			<xsl:when test="extension[@extension_name='loop_listing']">
        		<func:result>2</func:result>
			</xsl:when>
			<xsl:when test="extension[@extension_name='loop_media']">
        		<func:result>2</func:result>
			</xsl:when>
			<xsl:when test="extension[@extension_name='loop_table']">
        		<func:result>2</func:result>
			</xsl:when>
			<xsl:when test="extension[@extension_name='loop_task']">
        		<func:result>2</func:result>
			</xsl:when>
			<xsl:when test="extension[@extension_name='loop_area']">
        		<func:result>3</func:result>
			</xsl:when>
			<xsl:when test="extension[@extension_name='syntaxhighlight']">
        		<func:result>2</func:result>
			</xsl:when>

			<xsl:otherwise>
        		<func:result>1</func:result>
			</xsl:otherwise>

		</xsl:choose>
		

	</func:function>
		
	<xsl:template match="preblock">
		
	</xsl:template>

	
	<xsl:template match="space">
		<!--<xsl:element name="break">
			<xsl:attribute name="time">
				<xsl:text>1200ms</xsl:text>
			</xsl:attribute>
		</xsl:element>-->
	</xsl:template>		

	<xsl:template match="meta">

	</xsl:template>		
	
</xsl:stylesheet>