<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format"
	xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2004/07/xpath-functions"
	xmlns:xdt="http://www.w3.org/2004/07/xpath-datatypes" xmlns:fox="http://xml.apache.org/fop/extensions"
	xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:exsl="http://exslt.org/common"
	xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:func="http://exslt.org/functions"
	xmlns:php="http://php.net/xsl" xmlns:str="http://exslt.org/strings"
	xmlns:axf="http://www.antennahouse.com/names/XSL/Extensions"
	extension-element-prefixes="func php str" xmlns:functx="http://www.functx.com" exclude-result-prefixes="xhtml">

	<xsl:import href="pdf_params.xsl"></xsl:import>
	<xsl:import href="terms.xsl"></xsl:import>	
		
	<xsl:output method="xml" version="1.0" encoding="UTF-8"
		indent="yes"></xsl:output>

	<xsl:variable name="lang">
		<xsl:value-of select="/loop/meta/lang"></xsl:value-of>
	</xsl:variable>
	
	<xsl:template match="loop">
		<xsl:param name="cite_exists"><xsl:call-template name="cite_exists"></xsl:call-template></xsl:param>
		<xsl:param name="figure_exists"><xsl:call-template name="figure_exists"></xsl:call-template></xsl:param>
		<xsl:param name="table_exists"><xsl:call-template name="table_exists"></xsl:call-template></xsl:param>
		<xsl:param name="media_exists"><xsl:call-template name="media_exists"></xsl:call-template></xsl:param>
		<xsl:param name="formula_exists"><xsl:call-template name="formula_exists"></xsl:call-template></xsl:param>
		<xsl:param name="listing_exists" ><xsl:call-template name="listing_exists"></xsl:call-template></xsl:param>
		<xsl:param name="task_exists"><xsl:call-template name="task_exists"></xsl:call-template></xsl:param>			
		<xsl:param name="index_exists"><xsl:call-template name="index_exists"></xsl:call-template></xsl:param>
		<xsl:param name="glossary_exists"><xsl:call-template name="glossary_exists"></xsl:call-template></xsl:param>	
		<fo:root>
			<xsl:attribute name="hyphenate">true</xsl:attribute>
			<fo:layout-master-set>
				<fo:simple-page-master master-name="cover-page"
					page-height="{$pageheight}" page-width="{$pagewidth}" margin-top="10mm"
					margin-bottom="10mm" margin-left="25mm" margin-right="15mm">
					<fo:region-body margin-top="10mm" margin-bottom="15mm" />
				</fo:simple-page-master>
				<fo:simple-page-master master-name="full-page"
					page-height="{$pageheight}" page-width="{$pagewidth}" margin-top="10mm"
					margin-bottom="5mm" margin-left="25mm" margin-right="15mm">
					<fo:region-body margin-top="15mm" margin-bottom="15mm" />
					<fo:region-before extent="20mm" />
					<fo:region-after extent="15mm" />
				</fo:simple-page-master>
				<fo:simple-page-master master-name="default-page"
					page-height="{$pageheight}" page-width="{$pagewidth}" margin-top="10mm"
					margin-bottom="5mm" margin-left="25mm" margin-right="15mm">
					<fo:region-body margin-top="15mm" margin-bottom="15mm"
						margin-left="20mm"/>
					<fo:region-before extent="20mm" />
					<fo:region-after extent="15mm" />
				</fo:simple-page-master>
				<fo:simple-page-master master-name="full-page-2column"
					page-height="{$pageheight}" page-width="{$pagewidth}" margin-top="10mm"
					margin-bottom="5mm" margin-left="25mm" margin-right="15mm">
					<fo:region-body margin-top="15mm" margin-bottom="15mm" column-count="2" column-gap="10mm"/>
					<fo:region-before extent="20mm" />
					<fo:region-after extent="15mm" />
				</fo:simple-page-master>				
			</fo:layout-master-set>
			
			<xsl:call-template name="make-declarations"></xsl:call-template>
			
			<xsl:call-template name="page-sequence-cover"></xsl:call-template>	
			<xsl:call-template name="page-sequence-table-of-content"></xsl:call-template>
			<xsl:call-template name="page-sequence-contentpages"></xsl:call-template>				
		
			<xsl:if test="($cite_exists='1') or ($figure_exists='1') or ($table_exists='1') or ($media_exists='1') or ($formula_exists='1') or ($listing_exists='1') or ($task_exists='1') or ($index_exists='1') or ($glossary_exists='1')">
				<xsl:call-template name="page-sequence-appendix"></xsl:call-template>
			</xsl:if>
			
		</fo:root>
	</xsl:template>
	
		<xsl:template name="page-sequence-appendix">
		<xsl:param name="cite_exists"><xsl:call-template name="cite_exists"></xsl:call-template></xsl:param>
		<xsl:param name="figure_exists"><xsl:call-template name="figure_exists"></xsl:call-template></xsl:param>
		<xsl:param name="table_exists"><xsl:call-template name="table_exists"></xsl:call-template></xsl:param>
		<xsl:param name="media_exists"><xsl:call-template name="media_exists"></xsl:call-template></xsl:param>
		<xsl:param name="formula_exists"><xsl:call-template name="formula_exists"></xsl:call-template></xsl:param>
		<xsl:param name="listing_exists"><xsl:call-template name="listing_exists"></xsl:call-template></xsl:param>
		<xsl:param name="task_exists"><xsl:call-template name="task_exists"></xsl:call-template></xsl:param>
		<xsl:param name="index_exists"><xsl:call-template name="index_exists"></xsl:call-template></xsl:param>			
		<xsl:param name="glossary_exists"><xsl:call-template name="glossary_exists"></xsl:call-template></xsl:param>
		<fo:page-sequence master-reference="full-page" id="appendix_sequence">
			<fo:static-content font-family="{$font_family}" flow-name="xsl-region-before">
				<xsl:call-template name="default-header"></xsl:call-template>			
			</fo:static-content>			
			<fo:static-content font-family="{$font_family}" flow-name="xsl-region-after">
				<xsl:call-template name="default-footer"></xsl:call-template>
			</fo:static-content>
			<fo:flow font-family="{$font_family}" flow-name="xsl-region-body">
				<xsl:call-template name="page-content-appendix"></xsl:call-template>
				
				<!-- 
				<xsl:if test="$cite_exists='1'">
						<xsl:call-template name="page-content-bibliography"></xsl:call-template>
				</xsl:if>				
				-->
				<xsl:if test="$figure_exists='1'">
						<xsl:call-template name="page-content-list-of-objects">
						<xsl:with-param name="object_type">loop_figure</xsl:with-param>
					</xsl:call-template>
				</xsl:if>
				<xsl:if test="$table_exists='1'">
						<xsl:call-template name="page-content-list-of-objects">
						<xsl:with-param name="object_type">loop_table</xsl:with-param>
					</xsl:call-template>
				</xsl:if>
				<xsl:if test="$media_exists='1'">
						<xsl:call-template name="page-content-list-of-objects">
						<xsl:with-param name="object_type">loop_media</xsl:with-param>
					</xsl:call-template>
				</xsl:if>
				<xsl:if test="$formula_exists='1'">
						<xsl:call-template name="page-content-list-of-objects">
						<xsl:with-param name="object_type">loop_formula</xsl:with-param>
					</xsl:call-template>
				</xsl:if>
				<xsl:if test="$listing_exists='1'">
						<xsl:call-template name="page-content-list-of-objects">
						<xsl:with-param name="object_type">loop_listing</xsl:with-param>
					</xsl:call-template>
				</xsl:if>            
				<xsl:if test="$task_exists='1'">
						<xsl:call-template name="page-content-list-of-objects">
						<xsl:with-param name="object_type">loop_task</xsl:with-param>
					</xsl:call-template>
				</xsl:if>

			</fo:flow>
		</fo:page-sequence>
		<!-- 	
		<xsl:if test="$glossary_exists='1'">
		<fo:page-sequence master-reference="full-page" id="glossary_sequence">
			<fo:static-content font-family="{$font_family}" flow-name="xsl-region-before">
				<xsl:call-template name="default-header"></xsl:call-template>			
			</fo:static-content>			
			<fo:static-content font-family="{$font_family}" flow-name="xsl-region-after">
				<xsl:call-template name="default-footer"></xsl:call-template>
			</fo:static-content>
			<fo:flow font-family="{$font_family}" flow-name="xsl-region-body">
				<xsl:call-template name="page-content-glossary"></xsl:call-template>
			</fo:flow>
		</fo:page-sequence>	            	
        </xsl:if>			
		<xsl:if test="$index_exists='1'">
		<fo:page-sequence master-reference="full-page-2column" id="index_sequence">
			<fo:static-content font-family="{$font_family}" flow-name="xsl-region-before">
				<xsl:call-template name="default-header"></xsl:call-template>			
			</fo:static-content>			
			<fo:static-content font-family="{$font_family}" flow-name="xsl-region-after">
				<xsl:call-template name="default-footer"></xsl:call-template>
			</fo:static-content>
			<fo:flow font-family="{$font_family}" flow-name="xsl-region-body">		
            	<xsl:call-template name="page-content-index"></xsl:call-template>
			</fo:flow>
		</fo:page-sequence>	            	
        </xsl:if>       
	 	-->
	</xsl:template>			
	
	<xsl:template name="page-content-appendix">
		<fo:block id="appendix"></fo:block>
	</xsl:template>		
	
	<xsl:template name="make-declarations">
		<axf:document-info name="document-title" >
			<xsl:attribute name="value"><xsl:value-of select="/loop/meta/title"></xsl:value-of></xsl:attribute>
		</axf:document-info>
		
		<!-- ToDo: add more infos, see https://www.antennahouse.com/product/ahf65/ahf-ext.html#axf.document-info -->
	</xsl:template>		
	
	
	<!-- Page Sequence für Cover-Page -->
	<xsl:template name="page-sequence-cover">
		<fo:page-sequence master-reference="cover-page" id="cover_sequence">
			<fo:flow font-family="{$font_family}" flow-name="xsl-region-body">
				<xsl:call-template name="page-content-cover"></xsl:call-template>
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>	
	
	<!-- Page Content der Cover-Page -->
	<xsl:template name="page-content-cover">
		<fo:block text-align="right" font-size="26pt" font-weight="bold"
			id="cover" margin-bottom="10mm" margin-top="40mm" hyphenate="false">
			<xsl:value-of select="/loop/meta/title"></xsl:value-of>
		</fo:block>
		<fo:block text-align="right" font-size="14pt" font-weight="normal"
			margin-bottom="5mm">
			<xsl:value-of select="/loop/meta/url"></xsl:value-of>
		</fo:block>
		<fo:block text-align="right" font-size="12pt" margin-bottom="10mm">
			<xsl:value-of select="$word_state"></xsl:value-of>
			<xsl:text> </xsl:text>
			<xsl:value-of select="/loop/meta/date_generated"></xsl:value-of>
		</fo:block>
		
	</xsl:template>	
	
	
	<!-- Page Sequence für Inhaltsverzeichnis -->
	<xsl:template name="page-sequence-table-of-content">
		<fo:page-sequence master-reference="full-page"
			id="table_of_content_sequence">
			<fo:static-content font-family="{$font_family}"
				flow-name="xsl-region-before">
				<xsl:call-template name="default-header"></xsl:call-template>
			</fo:static-content>
			<fo:static-content font-family="{$font_family}"
				flow-name="xsl-region-after">
				<xsl:call-template name="default-footer"></xsl:call-template>
			</fo:static-content>
			<fo:flow font-family="{$font_family}" flow-name="xsl-region-body"
				text-align="justify" font-size="11.5pt" line-height="15.5pt"
				orphans="3">
				<xsl:call-template name="page-content-table-of-content"></xsl:call-template>
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>	
	
	<!-- Page Content des Inhaltsverzeichnises -->
	<xsl:template name="page-content-table-of-content">
		<xsl:param name="cite_exists"><xsl:call-template name="cite_exists"></xsl:call-template></xsl:param>
		<xsl:param name="figure_exists"><xsl:call-template name="figure_exists"></xsl:call-template></xsl:param>
		<xsl:param name="table_exists"><xsl:call-template name="table_exists"></xsl:call-template></xsl:param>	
		<xsl:param name="media_exists"><xsl:call-template name="media_exists"></xsl:call-template></xsl:param>
		<xsl:param name="formula_exists"><xsl:call-template name="formula_exists"></xsl:call-template></xsl:param>
		<xsl:param name="listing_exists"><xsl:call-template name="listing_exists"></xsl:call-template></xsl:param>
		<xsl:param name="task_exists"><xsl:call-template name="task_exists"></xsl:call-template></xsl:param>
		<xsl:param name="index_exists"><xsl:call-template name="index_exists"></xsl:call-template></xsl:param>
		<xsl:param name="glossary_exists"><xsl:call-template name="glossary_exists"></xsl:call-template></xsl:param>	
		<fo:block>
			<fo:marker marker-class-name="page-title-left">
				<xsl:value-of select="//loop/meta/title"></xsl:value-of>
			</fo:marker>
		</fo:block>
		<fo:block>
			<fo:marker marker-class-name="page-title-right">
				<xsl:value-of select="$word_content"></xsl:value-of>
			</fo:marker>
		</fo:block>
		<fo:block id="table_of_content">
			<xsl:call-template name="font_head"></xsl:call-template>
			<xsl:value-of select="$word_content"></xsl:value-of>
		</fo:block>
		
		<xsl:call-template name="make-toc"></xsl:call-template>	
		
		<xsl:if test="($cite_exists='1') or ($figure_exists='1') or ($table_exists='1') or ($media_exists='1') or ($task_exists='1') or ($index_exists='1') or ($glossary_exists='1')">
			<fo:block margin-bottom="1em"></fo:block>
			<fo:block>
				<xsl:call-template name="font_subsubhead"></xsl:call-template>
				<xsl:value-of select="$word_appendix"></xsl:value-of>
			</fo:block>		
		</xsl:if>		
		
		<xsl:if test="$figure_exists='1'">
			<fo:block text-align-last="justify">
				<xsl:call-template name="font_normal"></xsl:call-template>
				<fo:basic-link color="black">
					<xsl:attribute name="internal-destination">list_of_figures</xsl:attribute>
					<xsl:call-template name="appendix_number">
						<xsl:with-param name="content" select="'list_of_figures'"></xsl:with-param>
					</xsl:call-template>					
					<xsl:text> </xsl:text><xsl:value-of select="$word_list_of_figures"></xsl:value-of>
				</fo:basic-link>
				<fo:inline keep-together.within-line="always">
					<fo:leader leader-pattern="dots"></fo:leader>
					<fo:page-number-citation>
						<xsl:attribute name="ref-id">list_of_figures</xsl:attribute>
					</fo:page-number-citation>
				</fo:inline>
			</fo:block>		
		</xsl:if>	
		<xsl:if test="$table_exists='1'">
			<fo:block text-align-last="justify">
				<xsl:call-template name="font_normal"></xsl:call-template>
				<fo:basic-link color="black">
					<xsl:attribute name="internal-destination">list_of_tables</xsl:attribute>
					<xsl:call-template name="appendix_number">
						<xsl:with-param name="content" select="'list_of_tables'"></xsl:with-param>
					</xsl:call-template>					
					<xsl:text> </xsl:text><xsl:value-of select="$word_list_of_tables"></xsl:value-of>
				</fo:basic-link>
				<fo:inline keep-together.within-line="always">
					<fo:leader leader-pattern="dots"></fo:leader>
					<fo:page-number-citation>
						<xsl:attribute name="ref-id">list_of_tables</xsl:attribute>
					</fo:page-number-citation>
				</fo:inline>
			</fo:block>		
		</xsl:if>
		<xsl:if test="$media_exists='1'">
			<fo:block text-align-last="justify">
				<xsl:call-template name="font_normal"></xsl:call-template>
				<fo:basic-link color="black">
					<xsl:attribute name="internal-destination">list_of_media</xsl:attribute>
					<xsl:call-template name="appendix_number">
						<xsl:with-param name="content" select="'list_of_media'"></xsl:with-param>
					</xsl:call-template>					
					<xsl:text> </xsl:text><xsl:value-of select="$word_list_of_media"></xsl:value-of>
				</fo:basic-link>
				<fo:inline keep-together.within-line="always">
					<fo:leader leader-pattern="dots"></fo:leader>
					<fo:page-number-citation>
						<xsl:attribute name="ref-id">list_of_media</xsl:attribute>
					</fo:page-number-citation>
				</fo:inline>
			</fo:block>		
		</xsl:if>
		<xsl:if test="$formula_exists='1'">
			<fo:block text-align-last="justify">
				<xsl:call-template name="font_normal"></xsl:call-template>
				<fo:basic-link color="black">
					<xsl:attribute name="internal-destination">list_of_formulas</xsl:attribute>
					<xsl:call-template name="appendix_number">
						<xsl:with-param name="content" select="'list_of_formulas'"></xsl:with-param>
					</xsl:call-template>					
					<xsl:text> </xsl:text><xsl:value-of select="$word_list_of_formulas"></xsl:value-of>
				</fo:basic-link>
				<fo:inline keep-together.within-line="always">
					<fo:leader leader-pattern="dots"></fo:leader>
					<fo:page-number-citation>
						<xsl:attribute name="ref-id">list_of_formulas</xsl:attribute>
					</fo:page-number-citation>
				</fo:inline>
			</fo:block>		
		</xsl:if>	
		<xsl:if test="$listing_exists='1'">
			<fo:block text-align-last="justify">
				<xsl:call-template name="font_normal"></xsl:call-template>
				<fo:basic-link color="black">
					<xsl:attribute name="internal-destination">list_of_listings</xsl:attribute>
					<xsl:call-template name="appendix_number">
						<xsl:with-param name="content" select="'list_of_listings'"></xsl:with-param>
					</xsl:call-template>					
					<xsl:text> </xsl:text><xsl:value-of select="$word_list_of_listings"></xsl:value-of>
				</fo:basic-link>
				<fo:inline keep-together.within-line="always">
					<fo:leader leader-pattern="dots"></fo:leader>
					<fo:page-number-citation>
						<xsl:attribute name="ref-id">list_of_listings</xsl:attribute>
					</fo:page-number-citation>
				</fo:inline>
			</fo:block>		
		</xsl:if>
		<xsl:if test="$task_exists='1'">
			<fo:block text-align-last="justify">
				<xsl:call-template name="font_normal"></xsl:call-template>
				<fo:basic-link color="black">
					<xsl:attribute name="internal-destination">list_of_tasks</xsl:attribute>
					<xsl:call-template name="appendix_number">
						<xsl:with-param name="content" select="'list_of_tasks'"></xsl:with-param>
					</xsl:call-template>					
					<xsl:text> </xsl:text><xsl:value-of select="$word_list_of_tasks"></xsl:value-of>
				</fo:basic-link>
				<fo:inline keep-together.within-line="always">
					<fo:leader leader-pattern="dots"></fo:leader>
					<fo:page-number-citation>
						<xsl:attribute name="ref-id">list_of_tasks</xsl:attribute>
					</fo:page-number-citation>
				</fo:inline>
			</fo:block>		
		</xsl:if>
	</xsl:template>		
	
	<!-- LOOP_OBJECTS -->
	<xsl:template name="loop_object">
		<xsl:param name="object"></xsl:param>
		<xsl:variable name="objectid" select="@id"></xsl:variable>
		
		<fo:float>
			<fo:block>
				<fo:table table-layout="auto" border-style="solid" border-width="0pt" border-color="black" border-collapse="collapse" padding-start="0pt" padding-end="0pt" padding-top="4mm" padding-bottom="4mm"  padding-right="0pt" >
					<xsl:attribute name="id"><xsl:text>object</xsl:text><xsl:value-of select="@id"></xsl:value-of></xsl:attribute>
					<fo:table-column column-number="1" column-width="0.4mm"/><fo:table-column/>
					<fo:table-column column-number="2" /><fo:table-column/>
					<fo:table-body>		
						<fo:table-row keep-together.within-column="auto">
							<fo:table-cell number-columns-spanned="2">
								<fo:block  text-align="left" >
									<xsl:if test="ancestor::*[@extension_name='loop_area']">
										<xsl:attribute name="margin-left">15.5mm</xsl:attribute>
									</xsl:if>
									<xsl:apply-templates/> 
								</fo:block>
							</fo:table-cell>	
						</fo:table-row>
						<xsl:if test="count($object[@render]) = 0 or $object[@render!='none']">
							<fo:table-row keep-together.within-column="auto" >
								<fo:table-cell width="0.4mm" >
									<xsl:attribute name="background-color">
										<xsl:value-of select="$accent_color"></xsl:value-of>
									</xsl:attribute>
								</fo:table-cell>
								<fo:table-cell  text-align="left" padding-left="1mm" padding-right="2mm">
									<xsl:call-template name="font_object_title"></xsl:call-template>
		
									<fo:block text-align="left">
									<xsl:if test="ancestor::*[@extension_name='loop_area']">
										<xsl:attribute name="margin-left">15.5mm</xsl:attribute>
									</xsl:if>	
									<xsl:if test="count($object[@render]) = 0 or $object[@render!='title']">						
										<xsl:choose>
											<xsl:when test="$object[@extension_name='loop_figure']">
												<xsl:if test="count($object[@render]) = 0 or $object[@render!='marked']">		
													<xsl:value-of select="$icon_figure"></xsl:value-of>
													<xsl:text> </xsl:text>
												</xsl:if>
												<fo:inline font-weight="bold">
													<xsl:value-of select="$word_figure_short"></xsl:value-of>
												</fo:inline>
											</xsl:when>
											<xsl:when test="$object[@extension_name='loop_formula']">
												<xsl:if test="count($object[@render]) = 0 or $object[@render!='marked']">		
													<xsl:value-of select="$icon_formula"></xsl:value-of>
													<xsl:text> </xsl:text>
												</xsl:if>
												<fo:inline font-weight="bold">
													<xsl:value-of select="$word_formula_short"></xsl:value-of>
												</fo:inline>
											</xsl:when>
											<xsl:when test="$object[@extension_name='loop_listing']">
												<xsl:if test="count($object[@render]) = 0 or $object[@render!='marked']">		
													<xsl:value-of select="$icon_listing"></xsl:value-of>
													<xsl:text> </xsl:text>
												</xsl:if>
												<fo:inline font-weight="bold">
													<xsl:value-of select="$word_listing_short"></xsl:value-of>
												</fo:inline>
											</xsl:when>
											<xsl:when test="$object[@extension_name='loop_media']">
												<xsl:if test="count($object[@render]) = 0 or $object[@render!='marked']">		
													<xsl:choose>
														<xsl:when test="$object[@type='rollover']">
															<xsl:value-of select="$icon_rollover"></xsl:value-of>
														</xsl:when>
														<xsl:when test="$object[@type='video']">
															<xsl:value-of select="$icon_video"></xsl:value-of>
														</xsl:when>
														<xsl:when test="$object[@type='interaction']">
															<xsl:value-of select="$icon_interaction"></xsl:value-of>
														</xsl:when>
														<xsl:when test="$object[@type='click']">
															<xsl:value-of select="$icon_click"></xsl:value-of>
														</xsl:when>
														<xsl:when test="$object[@type='audio']">
															<xsl:value-of select="$icon_audio"></xsl:value-of>
														</xsl:when>
														<xsl:when test="$object[@type='animation']">
															<xsl:value-of select="$icon_animation"></xsl:value-of>
														</xsl:when>
														<xsl:when test="$object[@type='simulation']">
															<xsl:value-of select="$icon_simulation"></xsl:value-of>
														</xsl:when>
														<xsl:when test="$object[@type='dragdrop']">
															<xsl:value-of select="$icon_dragdrop"></xsl:value-of>
														</xsl:when>
														<xsl:when test="$object[@type='media']">
															<xsl:value-of select="$icon_media"></xsl:value-of>
														</xsl:when>
														<xsl:otherwise>
															<xsl:value-of select="$icon_media"></xsl:value-of>
														</xsl:otherwise>
													</xsl:choose>
													<xsl:text> </xsl:text>
												</xsl:if>		
												<fo:inline font-weight="bold">
													<xsl:value-of select="$word_media_short"></xsl:value-of>
												</fo:inline>
											</xsl:when>
											<xsl:when test="$object[@extension_name='loop_task']">
												<xsl:if test="count($object[@render]) = 0 or $object[@render!='marked']">		
													<xsl:value-of select="$icon_task"></xsl:value-of>
													<xsl:text> </xsl:text>
												</xsl:if>
												<fo:inline font-weight="bold">
													<xsl:value-of select="$word_task_short"></xsl:value-of>
												</fo:inline>
											</xsl:when>
											<xsl:when test="$object[@extension_name='loop_table']">
												<xsl:if test="count($object[@render]) = 0 or $object[@render!='marked']">		
													<xsl:value-of select="$icon_table"></xsl:value-of>
													<xsl:text> </xsl:text>
												</xsl:if>
												<fo:inline font-weight="bold">
													<xsl:value-of select="$word_table_short"></xsl:value-of>
												</fo:inline>
											</xsl:when>
											<xsl:otherwise>
											</xsl:otherwise>
										</xsl:choose>
									</xsl:if>
									
									<xsl:if test="count($object[@render]) = 0 or $object[@render!='title']">			
										<fo:inline font-weight="bold">
											<xsl:if test="//*/loop_object[@refid = $objectid]/object_number">
												<xsl:text> </xsl:text>
												<xsl:value-of select="//*/loop_object[@refid = $objectid]/object_number"></xsl:value-of>
											</xsl:if>
											<xsl:text>: </xsl:text>
										</fo:inline>
									</xsl:if>
										<xsl:choose>
											<xsl:when test="$object/descendant::extension[@extension_name='loop_title']">
												<xsl:apply-templates select="$object/descendant::extension[@extension_name='loop_title']" mode="loop_object"></xsl:apply-templates>
											</xsl:when>
											<xsl:otherwise>
												<xsl:value-of select="$object/@title"></xsl:value-of>	
											</xsl:otherwise>
										</xsl:choose>
									</fo:block>
		
									<xsl:if test="count($object[@render]) = 0 or $object[@render!='title']">	
										<xsl:if test="($object/@description) or ($object/descendant::extension[@extension_name='loop_description'])">
											<fo:block text-align="left">
												<xsl:if test="ancestor::*[@extension_name='loop_area']">
													<xsl:attribute name="margin-left">15.5mm</xsl:attribute>
												</xsl:if>
												<xsl:choose>
													<xsl:when test="$object/descendant::extension[@extension_name='loop_description']">
														<xsl:apply-templates select="$object/descendant::extension[@extension_name='loop_description']" mode="loop_object"></xsl:apply-templates>
													</xsl:when>
													<xsl:otherwise>
														<xsl:value-of select="$object/@description"></xsl:value-of>	
													</xsl:otherwise>
												</xsl:choose>
											</fo:block>		
										</xsl:if>
										
										<xsl:if test="($object/@copyright) or ($object/descendant::extension[@extension_name='loop_copyright'])">
											<fo:block text-align="left">
												<xsl:if test="ancestor::*[@extension_name='loop_area']">
													<xsl:attribute name="margin-left">15.5mm</xsl:attribute>
												</xsl:if>
												<xsl:choose>
													<xsl:when test="$object/descendant::extension[@extension_name='loop_copyright']">
														<xsl:apply-templates select="$object/descendant::extension[@extension_name='loop_copyright']" mode="loop_object"></xsl:apply-templates>
													</xsl:when>
													<xsl:otherwise>
														<xsl:value-of select="$object/@copyright"></xsl:value-of>	
													</xsl:otherwise>
												</xsl:choose>
											</fo:block>		
										</xsl:if>
									</xsl:if>
								</fo:table-cell>
							</fo:table-row>
						</xsl:if>
					</fo:table-body>
				</fo:table>
			</fo:block>
		</fo:float>
	</xsl:template>
	
	<xsl:template name="page-content-list-of-objects">
		<xsl:param name="object_type"></xsl:param>
		<xsl:param name="cite_exists"><xsl:call-template name="cite_exists"></xsl:call-template></xsl:param>
		<xsl:if test="$cite_exists='1'">
			<fo:block break-before="page"></fo:block>
		</xsl:if>		
		<fo:block>
			<fo:marker marker-class-name="page-title-left">
				<xsl:value-of select="$word_appendix"></xsl:value-of>
			</fo:marker>
		</fo:block>
		<fo:block>
			<fo:marker marker-class-name="page-title-right">
					<xsl:choose>
						<xsl:when test="$object_type='loop_figure'">
							<xsl:call-template name="appendix_number">
								<xsl:with-param name="content" select="'list_of_figures'"></xsl:with-param>
							</xsl:call-template>
						</xsl:when>
							<xsl:when test="$object_type='loop_formula'">
							<xsl:call-template name="appendix_number">
								<xsl:with-param name="content" select="'list_of_formulas'"></xsl:with-param>
							</xsl:call-template>
						</xsl:when>
						<xsl:when test="$object_type='loop_listing'">
							<xsl:call-template name="appendix_number">
								<xsl:with-param name="content" select="'list_of_listings'"></xsl:with-param>
							</xsl:call-template>
						</xsl:when>
						<xsl:when test="$object_type='loop_media'">
							<xsl:call-template name="appendix_number">
								<xsl:with-param name="content" select="'list_of_media'"></xsl:with-param>
							</xsl:call-template>
						</xsl:when>
						<xsl:when test="$object_type='loop_task'">
							<xsl:call-template name="appendix_number">
								<xsl:with-param name="content" select="'list_of_tasks'"></xsl:with-param>
							</xsl:call-template>
						</xsl:when>
						<xsl:when test="$object_type='loop_table'">
							<xsl:call-template name="appendix_number">
								<xsl:with-param name="content" select="'list_of_tables'"></xsl:with-param>
							</xsl:call-template>
						</xsl:when>
						<xsl:otherwise>
						</xsl:otherwise>
					</xsl:choose>
				<xsl:text> </xsl:text>				
				<xsl:choose>
					<xsl:when test="$object_type='loop_figure'">
						<xsl:value-of select="$word_list_of_figures"></xsl:value-of>
					</xsl:when>
					<xsl:when test="$object_type='loop_formula'">
						<xsl:value-of select="$word_list_of_formulas"></xsl:value-of>
					</xsl:when>
					<xsl:when test="$object_type='loop_listing'">
						<xsl:value-of select="$word_list_of_listings"></xsl:value-of>
					</xsl:when>
					<xsl:when test="$object_type='loop_media'">
						<xsl:value-of select="$word_list_of_media"></xsl:value-of>
					</xsl:when>
					<xsl:when test="$object_type='loop_task'">
						<xsl:value-of select="$word_list_of_tasks"></xsl:value-of>
					</xsl:when>
					<xsl:when test="$object_type='loop_table'">
						<xsl:value-of select="$word_list_of_tables"></xsl:value-of>
					</xsl:when>
					<xsl:otherwise>
					</xsl:otherwise>
				</xsl:choose>
			</fo:marker>
		</fo:block>
		<xsl:choose>
			<xsl:when test="$object_type='loop_figure'">
				<fo:block id="list_of_figures" keep-with-next="always" margin-bottom="5mm" margin-top="15mm">
					<xsl:call-template name="font_head"></xsl:call-template>
						<xsl:call-template name="appendix_number">
							<xsl:with-param name="content" select="'list_of_figures'"></xsl:with-param>
						</xsl:call-template>
						<xsl:text> </xsl:text>			
					<xsl:value-of select="$word_list_of_figures"></xsl:value-of>
				</fo:block>
			</xsl:when>
			<xsl:when test="$object_type='loop_formula'">
				<fo:block id="list_of_formulas" keep-with-next="always" margin-bottom="3mm" margin-top="15mm">
					<xsl:call-template name="font_head"></xsl:call-template>
						<xsl:call-template name="appendix_number">
							<xsl:with-param name="content" select="'list_of_formulas'"></xsl:with-param>
						</xsl:call-template>
						<xsl:text> </xsl:text>			
					<xsl:value-of select="$word_list_of_formulas"></xsl:value-of>
				</fo:block>
			</xsl:when>
			<xsl:when test="$object_type='loop_listing'">
				<fo:block id="list_of_listings" keep-with-next="always" margin-bottom="3mm" margin-top="15mm">
					<xsl:call-template name="font_head"></xsl:call-template>
						<xsl:call-template name="appendix_number">
							<xsl:with-param name="content" select="'list_of_listings'"></xsl:with-param>
						</xsl:call-template>
						<xsl:text> </xsl:text>			
					<xsl:value-of select="$word_list_of_listings"></xsl:value-of>
				</fo:block>
			</xsl:when>
			<xsl:when test="$object_type='loop_media'">
				<fo:block id="list_of_media" keep-with-next="always" margin-bottom="3mm" margin-top="15mm">
					<xsl:call-template name="font_head"></xsl:call-template>
						<xsl:call-template name="appendix_number">
							<xsl:with-param name="content" select="'list_of_media'"></xsl:with-param>
						</xsl:call-template>
						<xsl:text> </xsl:text>			
					<xsl:value-of select="$word_list_of_media"></xsl:value-of>
				</fo:block>
			</xsl:when>
			<xsl:when test="$object_type='loop_table'">
				<fo:block id="list_of_tables" keep-with-next="always" margin-bottom="3mm" margin-top="15mm">
					<xsl:call-template name="font_head"></xsl:call-template>
						<xsl:call-template name="appendix_number">
							<xsl:with-param name="content" select="'list_of_tables'"></xsl:with-param>
						</xsl:call-template>
						<xsl:text> </xsl:text>			
					<xsl:value-of select="$word_list_of_tables"></xsl:value-of>
				</fo:block>
			</xsl:when>
			<xsl:when test="$object_type='loop_task'">
				<fo:block id="list_of_tasks" keep-with-next="always" margin-bottom="3mm" margin-top="15mm">
					<xsl:call-template name="font_head"></xsl:call-template>
						<xsl:call-template name="appendix_number">
							<xsl:with-param name="content" select="'list_of_tasks'"></xsl:with-param>
						</xsl:call-template>
						<xsl:text> </xsl:text>			
					<xsl:value-of select="$word_list_of_tasks"></xsl:value-of>
				</fo:block>
			</xsl:when>
			<xsl:otherwise>
			</xsl:otherwise>
		</xsl:choose>
		 		
		<fo:table width="170mm" table-layout="auto" margin-bottom="8mm">
			<fo:table-body>
				<xsl:choose>
					<xsl:when test="$object_type='loop_figure'">
						<xsl:apply-templates select="//*/extension[@extension_name='loop_figure']" mode="list_of_objects">
							<xsl:with-param name="object_type" select="$object_type"></xsl:with-param> 
                </xsl:apply-templates>
					</xsl:when>
					<xsl:when test="$object_type='loop_formula'">
						<xsl:apply-templates select="//*/extension[@extension_name='loop_formula']" mode="list_of_objects">
							<xsl:with-param name="object_type" select="$object_type"></xsl:with-param> 
						</xsl:apply-templates>
					</xsl:when>
					<xsl:when test="$object_type='loop_listing'">
						<xsl:apply-templates select="//*/extension[@extension_name='loop_listing']" mode="list_of_objects">
							<xsl:with-param name="object_type" select="$object_type"></xsl:with-param> 
                </xsl:apply-templates>
					</xsl:when>
					<xsl:when test="$object_type='loop_media'">
						<xsl:apply-templates select="//*/extension[@extension_name='loop_media']" mode="list_of_objects">
							<xsl:with-param name="object_type" select="$object_type"></xsl:with-param> 
                </xsl:apply-templates>
					</xsl:when>
					<xsl:when test="$object_type='loop_task'">
						<xsl:apply-templates select="//*/extension[@extension_name='loop_task']" mode="list_of_objects">
							<xsl:with-param name="object_type" select="$object_type"></xsl:with-param> 
                </xsl:apply-templates>
					</xsl:when>
					<xsl:when test="$object_type='loop_table'">
						<xsl:apply-templates select="//*/extension[@extension_name='loop_table']" mode="list_of_objects">
							<xsl:with-param name="object_type" select="$object_type"></xsl:with-param> 
                </xsl:apply-templates>
					</xsl:when>
					<xsl:otherwise>
					</xsl:otherwise>
				</xsl:choose>
			</fo:table-body>
		</fo:table>	
	</xsl:template>	
		
	<xsl:template match="extension" mode="list_of_objects">
		<xsl:param name="object_type"></xsl:param>
		<xsl:variable name="objectid" select="@id"></xsl:variable>

		<xsl:if test="//*/loop_object[@refid = $objectid]"> <!-- Check if object is in DB -->
				
			<fo:table-row>
				<fo:table-cell>
					<xsl:attribute name="width">
						<xsl:choose>
							<xsl:when test="$object_type='loop_figure'">
								30mm
							</xsl:when>
							<xsl:otherwise>
								10mm
							</xsl:otherwise>
						</xsl:choose>	
					</xsl:attribute>
					<xsl:choose>
						<xsl:when test="$object_type='loop_figure'">
							<fo:block>
								<fo:basic-link>
									<xsl:attribute name="internal-destination"><xsl:value-of select="generate-id()"></xsl:value-of></xsl:attribute>
									<fo:block>
									<xsl:if test="php:function('xsl_transform_imagepath', descendant::link/target)!=''">
										<fo:external-graphic scaling="uniform" content-width="24mm" content-height="scale-to-fit" max-height="20mm">
											<xsl:attribute name="src"><xsl:value-of select="php:function('xsl_transform_imagepath', descendant::link/target)"></xsl:value-of></xsl:attribute>
										</fo:external-graphic>
									</xsl:if>
									</fo:block>
								</fo:basic-link>
							</fo:block>
						</xsl:when>
						<xsl:otherwise>
						</xsl:otherwise>
					</xsl:choose>	
				</fo:table-cell>

				<fo:table-cell width="140mm">
					<fo:block text-align-last="justify" text-align="justify">
						<fo:basic-link color="black">
							<xsl:attribute name="internal-destination"><xsl:value-of select="generate-id()"></xsl:value-of></xsl:attribute>
							
							<fo:inline font-weight="bold">
								<xsl:choose>
									<xsl:when test="$object_type='loop_figure'">
										<xsl:value-of select="$word_figure_short"></xsl:value-of>
									</xsl:when>
									<xsl:when test="$object_type='loop_formula'">
										<xsl:value-of select="$word_formula_short"></xsl:value-of>
									</xsl:when>
									<xsl:when test="$object_type='loop_listing'">
										<xsl:value-of select="$word_listing_short"></xsl:value-of>
									</xsl:when>
									<xsl:when test="$object_type='loop_media'">
										<xsl:value-of select="$word_media_short"></xsl:value-of>
									</xsl:when>
									<xsl:when test="$object_type='loop_task'">
										<xsl:value-of select="$word_task_short"></xsl:value-of>
									</xsl:when>
									<xsl:when test="$object_type='loop_table'">
										<xsl:value-of select="$word_table_short"></xsl:value-of>
									</xsl:when>
									<xsl:otherwise>
									</xsl:otherwise>
								</xsl:choose>	
								
								<xsl:if test="//*/loop_object[@refid = $objectid]/object_number">
									<xsl:text> </xsl:text>
									<xsl:value-of select="//*/loop_object[@refid = $objectid]/object_number"></xsl:value-of>
								</xsl:if>
								<xsl:text>: </xsl:text>
							</fo:inline>	
									
							<fo:inline>	
								<xsl:choose>
									<xsl:when test="descendant::extension[@extension_name='loop_figure_title']">
										<xsl:apply-templates select="descendant::extension[@extension_name='loop_figure_title']" mode="infigure"></xsl:apply-templates>
									</xsl:when>
									<xsl:when test="descendant::extension[@extension_name='loop_title']">
										<xsl:apply-templates select="descendant::extension[@extension_name='loop_title']"></xsl:apply-templates>
									</xsl:when>
									<xsl:otherwise>
										<xsl:if test="@title">
											<xsl:value-of select="@title"></xsl:value-of>
										</xsl:if>
									</xsl:otherwise>
								</xsl:choose>	
								<fo:leader leader-pattern="dots"></fo:leader>
								<fo:page-number-citation>
									<xsl:attribute name="ref-id"><xsl:value-of select="generate-id()"></xsl:value-of></xsl:attribute>
								</fo:page-number-citation>
							</fo:inline>
						</fo:basic-link>					
					</fo:block>
				</fo:table-cell>			
			</fo:table-row>
		</xsl:if>
	</xsl:template>	
	<!-- /LOOP_OBJECTS -->
	
	<xsl:template name="make-toc">
		<xsl:apply-templates select="toc"  mode="toc"></xsl:apply-templates>
	</xsl:template>
	
	<xsl:template match="toc" mode="toc">
		<xsl:apply-templates select="chapter"  mode="toc"></xsl:apply-templates>
	</xsl:template>
	
	<xsl:template match="chapter" mode="toc">
		<xsl:apply-templates select="page"  mode="toc"></xsl:apply-templates>
	</xsl:template>

	<xsl:template match="page" mode="toc">
		<fo:block text-align-last="justify">
			<xsl:call-template name="font_normal"></xsl:call-template>	
			<xsl:if test="@toclevel &gt; 0">
				<xsl:attribute name="margin-left">
					<xsl:value-of select="@toclevel - 1"></xsl:value-of><xsl:text>em</xsl:text>
				</xsl:attribute>
			</xsl:if>
			
			<fo:basic-link color="black">
				<xsl:attribute name="internal-destination" >
				<!--  <xsl:value-of select="@title"></xsl:value-of>
				<xsl:value-of select="generate-id()"/> -->
				<xsl:value-of select="@id"></xsl:value-of>
				</xsl:attribute>
				<xsl:value-of select="@tocnumber"></xsl:value-of>
				<xsl:text> </xsl:text>
				<xsl:value-of select="@toctext"></xsl:value-of>
			</fo:basic-link>
			<fo:inline keep-together.within-line="always">
				<fo:leader leader-pattern="dots"></fo:leader>
				<fo:page-number-citation>
					<xsl:attribute name="ref-id">
					<!-- <xsl:value-of select="@title"></xsl:value-of>
					<xsl:value-of select="generate-id()"/> -->
					<xsl:value-of select="@id"></xsl:value-of>			
					</xsl:attribute>
				</fo:page-number-citation>
			</fo:inline>				
		</fo:block>
		<xsl:apply-templates select="chapter"  mode="toc"></xsl:apply-templates>	
		
	</xsl:template>			
	
	<!-- Page Sequence für Wiki-Seiten -->
	<xsl:template name="page-sequence-contentpages">
		<fo:page-sequence master-reference="default-page"
			id="contentpages_sequence">
			<fo:static-content font-family="{$font_family}"
				flow-name="xsl-region-before">
				<xsl:call-template name="default-header"></xsl:call-template>
			</fo:static-content>
			<fo:static-content font-family="{$font_family}"
				flow-name="xsl-region-after">
				<xsl:call-template name="default-footer"></xsl:call-template>
			</fo:static-content>
			<fo:flow font-family="{$font_family}" flow-name="xsl-region-body"
				text-align="justify" font-size="11.5pt" line-height="15.5pt"
				orphans="3">
				<xsl:call-template name="page-content-contentpages"></xsl:call-template>
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>

	<!-- Page Content einer Wiki-Seite -->
	<xsl:template name="page-content-contentpages">
		<xsl:apply-templates select="articles/article"></xsl:apply-templates>
	</xsl:template>	
	
	<!-- Page Content einer Wiki-Seite -->
	<xsl:template match="article">
		<xsl:variable name="toclevel" select="@toclevel"></xsl:variable>
		<xsl:choose>
			<xsl:when test="@toclevel=''">
				<xsl:apply-templates></xsl:apply-templates>
			</xsl:when>
			<xsl:otherwise>
				<fo:block >
					<xsl:attribute name="id">
						<!-- <xsl:value-of select="generate-id()"></xsl:value-of> -->
						<xsl:value-of select="@id"></xsl:value-of>
					</xsl:attribute>
					<xsl:choose>
						<xsl:when test="$toclevel &lt; 2"> 
							<xsl:attribute name="break-before">page</xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<fo:block margin-top="10mm">
							</fo:block>		
						</xsl:otherwise>
					</xsl:choose>
					<fo:block>
						<fo:marker marker-class-name="page-title-left">
						<xsl:choose>
							<xsl:when test="@toclevel=0">
								<xsl:value-of select="//loop/@title"></xsl:value-of>
							</xsl:when>
							<xsl:when test="@toclevel=1">
								<xsl:value-of select="//loop/@title"></xsl:value-of>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="preceding-sibling::node()[@toclevel &lt; $toclevel][1]/@tocnumber"></xsl:value-of>
								<xsl:text> </xsl:text>
								<xsl:value-of select="preceding-sibling::node()[@toclevel &lt; $toclevel][1]/@toctext"></xsl:value-of>
							</xsl:otherwise>					
						</xsl:choose>
						</fo:marker>
					</fo:block>
					<fo:block>
						<fo:marker marker-class-name="page-title-right">
							<xsl:value-of select="@tocnumber"></xsl:value-of>
							<xsl:text> </xsl:text>
							<xsl:choose>
								<xsl:when test="string-length(@toctext) &gt; 63">
									<xsl:value-of select="concat(substring(@toctext,0,60),'...')"></xsl:value-of>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="@toctext"></xsl:value-of>
								</xsl:otherwise>
							</xsl:choose>
						</fo:marker>
					</fo:block>
					<fo:block keep-with-next.within-page="always">
						<xsl:call-template name="font_head"></xsl:call-template>
						<xsl:value-of select="@tocnumber"></xsl:value-of>
						<xsl:text> </xsl:text>
						<xsl:value-of select="@toctext"></xsl:value-of>
					</fo:block>
					<fo:block keep-with-previous.within-page="always">
						<xsl:call-template name="font_normal"></xsl:call-template>
						<xsl:apply-templates></xsl:apply-templates>
					</fo:block>
				</fo:block>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>	
	
	<!-- Default Header -->
	<xsl:template name="default-header">
		<fo:table table-layout="fixed" width="100%" margin-bottom="2mm">
			<fo:table-body>
				<fo:table-row>
					<fo:table-cell text-align="left">
						<fo:block line-height="13pt" margin-bottom="-3mm"
							font-weight="bold">
							<fo:retrieve-marker retrieve-class-name="page-title-left"
								retrieve-position="first-starting-within-page"
								retrieve-boundary="page-sequence"></fo:retrieve-marker>
						</fo:block>
					</fo:table-cell>
					<fo:table-cell text-align="right">
						<fo:block line-height="13pt" margin-bottom="-3mm">
							<fo:retrieve-marker retrieve-class-name="page-title-right"
								retrieve-position="first-including-carryover" retrieve-boundary="page-sequence"></fo:retrieve-marker>
						</fo:block>
					</fo:table-cell>
				</fo:table-row>
			</fo:table-body>
		</fo:table>
		<fo:block>
			<fo:leader leader-pattern="rule" leader-length="100%"
				rule-thickness="0.5pt" rule-style="solid" color="black"
				display-align="after"></fo:leader>
		</fo:block>
	</xsl:template>	
	
	<!-- Default Footer -->
	<xsl:template name="default-footer">
		<xsl:param name="last-page-sequence-name">
			<xsl:call-template name="last-page-sequence-name"></xsl:call-template>
		</xsl:param>
		<fo:block>
			<fo:leader leader-pattern="rule" leader-length="100%"
				rule-thickness="0.5pt" rule-style="solid" color="black"
				display-align="before"></fo:leader>
		</fo:block>
		<fo:block text-align="right">
			<fo:page-number></fo:page-number>
			/
			<fo:page-number-citation-last ref-id="{$last-page-sequence-name}"></fo:page-number-citation-last>
		</fo:block>
	</xsl:template>
		
	<xsl:template name="last-page-sequence-name">
		<xsl:param name="cite_exists"><xsl:call-template name="cite_exists"></xsl:call-template></xsl:param>
		<xsl:param name="figure_exists"><xsl:call-template name="figure_exists"></xsl:call-template></xsl:param>
		<xsl:param name="table_exists"><xsl:call-template name="table_exists"></xsl:call-template></xsl:param>
		<xsl:param name="media_exists"><xsl:call-template name="media_exists"></xsl:call-template></xsl:param>
		<xsl:param name="formula_exists"><xsl:call-template name="formula_exists"></xsl:call-template></xsl:param>
		<xsl:param name="listing_exists"><xsl:call-template name="listing_exists"></xsl:call-template></xsl:param>
		<xsl:param name="task_exists"><xsl:call-template name="task_exists"></xsl:call-template></xsl:param>			
		<xsl:param name="index_exists"><xsl:call-template name="index_exists"></xsl:call-template></xsl:param>		
		<xsl:param name="glossary_exists"><xsl:call-template name="glossary_exists"></xsl:call-template></xsl:param>
		
		
		<xsl:choose>
			<xsl:when test="($glossary_exists='1')">
				<xsl:text>glossary_sequence</xsl:text>
			</xsl:when>		
			<xsl:when test="($index_exists='1')">
				<xsl:text>index_sequence</xsl:text>
			</xsl:when>
			<xsl:when test="($cite_exists='1') or ($figure_exists='1') or ($table_exists='1') or ($media_exists='1') or ($formula_exists='1') or ($listing_exists='1') or ($task_exists='1') or ($glossary_exists='1')">
				<xsl:text>appendix_sequence</xsl:text>
			</xsl:when>			
			<xsl:otherwise>
				<xsl:text>contentpages_sequence</xsl:text>		
			</xsl:otherwise>
		</xsl:choose>	
	</xsl:template>
	
	
<xsl:template name="font_icon">
		<xsl:attribute name="font-size" >8.5pt</xsl:attribute>
		<xsl:attribute name="font-weight" >bold</xsl:attribute>
		<xsl:attribute name="line-height" >12pt</xsl:attribute>
		<xsl:attribute name="margin-bottom" >1mm</xsl:attribute>
	</xsl:template>	

	<xsl:template name="font_small">
		<xsl:attribute name="font-size">9.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">normal</xsl:attribute>
		<xsl:attribute name="line-height">12.5pt</xsl:attribute>
	</xsl:template>

	<xsl:template name="font_normal">
		<xsl:attribute name="font-size">11.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">normal</xsl:attribute>
		<xsl:attribute name="line-height">18.5pt</xsl:attribute>
	</xsl:template>

	<xsl:template name="font_big">
		<xsl:attribute name="font-size">12.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">normal</xsl:attribute>
		<xsl:attribute name="line-height">18.5pt</xsl:attribute>
	</xsl:template>
	
	<xsl:template name="font_subsubsubsubhead">
		<xsl:attribute name="font-size">11.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">bold</xsl:attribute>
		<xsl:attribute name="line-height">18.5pt</xsl:attribute>
	</xsl:template>	

	<xsl:template name="font_subsubsubhead">
		<xsl:attribute name="font-size">11.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">bold</xsl:attribute>
		<xsl:attribute name="line-height">18.5pt</xsl:attribute>
		<xsl:attribute name="margin-top">7pt</xsl:attribute>
	</xsl:template>	
		
	<xsl:template name="font_subsubhead">
		<xsl:attribute name="font-size">12.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">bold</xsl:attribute>
		<xsl:attribute name="line-height">18.5pt</xsl:attribute>
		<xsl:attribute name="margin-top">7pt</xsl:attribute>
	</xsl:template>

	<xsl:template name="font_subhead">
		<xsl:attribute name="font-size">13.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">bold</xsl:attribute>
		<xsl:attribute name="line-height">15.5pt</xsl:attribute>
		<xsl:attribute name="margin-top">7pt</xsl:attribute>
	</xsl:template>

	<xsl:template name="font_head">
		<xsl:attribute name="font-size">14.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">bold</xsl:attribute>
		<xsl:attribute name="line-height">16.5pt</xsl:attribute>
		<xsl:attribute name="margin-top">7pt</xsl:attribute>
	</xsl:template>	
	
	<xsl:template name="font_object_title">
		<xsl:attribute name="font-size">9.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">normal</xsl:attribute>
		<xsl:attribute name="line-height">12.5pt</xsl:attribute>
	</xsl:template>	
	
	<xsl:template match="paragraph">
		<xsl:choose>
			<xsl:when test="preceding::*[1][name()='heading' and (@level='4' or @level='5')]">
				<fo:block >
					<xsl:call-template name="font_normal"></xsl:call-template>
					<xsl:apply-templates></xsl:apply-templates>
				</fo:block>	
			</xsl:when>
			<xsl:otherwise>
	
				<fo:block margin-top="7pt">
					<xsl:call-template name="font_normal"></xsl:call-template>
					<xsl:apply-templates></xsl:apply-templates>
				</fo:block>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="preblock" >
		<xsl:apply-templates></xsl:apply-templates>
	</xsl:template>

	<xsl:template match="preline" >
		<fo:block font-family="{$font_family}">
	    	<xsl:apply-templates></xsl:apply-templates>
    	</fo:block>
	</xsl:template>		
	
	<xsl:template match="space">
		<xsl:text> </xsl:text>
	</xsl:template>		
	
	<xsl:template match="br">
		<xsl:choose>
			<xsl:when test="preceding::node()[1][name()='br']">
				<fo:block white-space-collapse="false" white-space-treatment="preserve" font-size="0pt" >.</fo:block>	
			</xsl:when>
			<xsl:otherwise>
				<fo:block></fo:block>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="xhtml:br">
		<xsl:choose>
			<xsl:when test="preceding::node()[1][name()='xhtml:br']">
				<fo:block white-space-collapse="false" white-space-treatment="preserve" font-size="0pt" >.</fo:block>	
			</xsl:when>
			<xsl:otherwise>
				<fo:block></fo:block>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>		
	
	<xsl:template match="sub">
		<fo:inline vertical-align="sub" font-size="0.8em"><xsl:apply-templates></xsl:apply-templates></fo:inline>
	</xsl:template>	
	
	<xsl:template match="sup">
		<fo:inline vertical-align="super" font-size="0.8em"><xsl:apply-templates></xsl:apply-templates></fo:inline>
	</xsl:template>	

	<xsl:template match="xhtml:sub">
		<fo:inline vertical-align="sub" font-size="0.8em"><xsl:apply-templates></xsl:apply-templates></fo:inline>
	</xsl:template>	
	
	<xsl:template match="xhtml:sup">
		<fo:inline vertical-align="super" font-size="0.8em"><xsl:apply-templates></xsl:apply-templates></fo:inline>
	</xsl:template>	
	
	<xsl:template match="big">
		<fo:inline>
			<xsl:call-template name="font_big"></xsl:call-template>
			<xsl:apply-templates></xsl:apply-templates>
		</fo:inline>
	</xsl:template>	
	
	<xsl:template match="small">
		<fo:inline>
			<xsl:call-template name="font_small"></xsl:call-template>
			<xsl:apply-templates></xsl:apply-templates>
		</fo:inline>
	</xsl:template>			

	<xsl:template match="xhtml:big">
		<fo:inline>
			<xsl:call-template name="font_big"></xsl:call-template>
			<xsl:apply-templates></xsl:apply-templates>
		</fo:inline>
	</xsl:template>	
	
	<xsl:template match="xhtml:small">
		<fo:inline>
			<xsl:call-template name="font_small"></xsl:call-template>
			<xsl:apply-templates></xsl:apply-templates>
		</fo:inline>
	</xsl:template>		

	<xsl:template match="bold">
		<fo:inline font-weight="bold">
			<xsl:apply-templates></xsl:apply-templates>
		</fo:inline>
	</xsl:template>
	<xsl:template match="b">
		<fo:inline font-weight="bold">
			<xsl:apply-templates></xsl:apply-templates>
		</fo:inline>
	</xsl:template>
	<xsl:template match="strong">
		<fo:inline font-weight="bold">
			<xsl:apply-templates></xsl:apply-templates>
		</fo:inline>
	</xsl:template>	

	<xsl:template match="italics">
		<fo:inline font-style="italic">
			<xsl:apply-templates></xsl:apply-templates>
		</fo:inline>
	</xsl:template>	
	



	<!-- Loop Area -->
	<xsl:template match="extension[@extension_name='loop_area']">
		<fo:block border-left="solid 0.4mm" margin-bottom="3mm" margin-top="3mm" padding="3mm 3mm 3mm 3mm" page-break-before="auto">
			<xsl:attribute name="border-color">
				<xsl:value-of select="$accent_color"></xsl:value-of>
			</xsl:attribute>

			<fo:block keep-with-next.within-page="always" margin-bottom="2mm" padding-bottom="1mm">
				<!-- ICON IMG -->
				<fo:inline font-size="x-large" padding-right="2mm">
					<xsl:choose> <!-- todo: trying to find a way to do this a much shorter way -->
						<xsl:when test="@type='task'"><xsl:value-of select="$icon_task"></xsl:value-of></xsl:when>
						<xsl:when test="@type='timerequirement'"><xsl:value-of select="$icon_timerequirement"></xsl:value-of></xsl:when>
						<xsl:when test="@type='learningobjectives'"><xsl:value-of select="$icon_learningobjectives"></xsl:value-of></xsl:when>
						<xsl:when test="@type='arrangement'"><xsl:value-of select="$icon_arrangement"></xsl:value-of></xsl:when>
						<xsl:when test="@type='example'"><xsl:value-of select="$icon_example"></xsl:value-of></xsl:when>
						<xsl:when test="@type='reflection'"><xsl:value-of select="$icon_reflection"></xsl:value-of></xsl:when>
						<xsl:when test="@type='notice'"><xsl:value-of select="$icon_notice"></xsl:value-of></xsl:when>
						<xsl:when test="@type='important'"><xsl:value-of select="$icon_important"></xsl:value-of></xsl:when>
						<xsl:when test="@type='annotation'"><xsl:value-of select="$icon_annotation"></xsl:value-of></xsl:when>
						<xsl:when test="@type='definition'"><xsl:value-of select="$icon_definition"></xsl:value-of></xsl:when>
						<xsl:when test="@type='formula'"><xsl:value-of select="$icon_formula"></xsl:value-of></xsl:when>
						<xsl:when test="@type='markedsentence'"><xsl:value-of select="$icon_markedsentence"></xsl:value-of></xsl:when>
						<xsl:when test="@type='sourcecode'"><xsl:value-of select="$icon_sourcecode"></xsl:value-of></xsl:when>
						<xsl:when test="@type='summary'"><xsl:value-of select="$icon_summary"></xsl:value-of></xsl:when>
						<xsl:when test="@type='indentation'"><xsl:value-of select="$icon_indentation"></xsl:value-of></xsl:when>
						<xsl:when test="@type='norm'"><xsl:value-of select="$icon_norm"></xsl:value-of></xsl:when>
						<xsl:when test="@type='law'"><xsl:value-of select="$icon_law"></xsl:value-of></xsl:when>
						<xsl:when test="@type='question'"><xsl:value-of select="$icon_question"></xsl:value-of></xsl:when>
						<xsl:when test="@type='practice'"><xsl:value-of select="$icon_practice"></xsl:value-of></xsl:when>
						<xsl:when test="@type='exercise'"><xsl:value-of select="$icon_exercise"></xsl:value-of></xsl:when>
						<xsl:when test="@type='websource'"><xsl:value-of select="$icon_websource"></xsl:value-of></xsl:when>
						<xsl:when test="@type='experiment'"><xsl:value-of select="$icon_experiment"></xsl:value-of></xsl:when>
						<xsl:when test="@type='citation'"><xsl:value-of select="$icon_citation"></xsl:value-of></xsl:when>
						
						<xsl:otherwise>
							<xsl:if test="@icon">
								<xsl:variable name="iconfilename"><xsl:value-of select="@icon"></xsl:value-of></xsl:variable>
							
								<xsl:if test="php:function('xsl_transform_imagepath', $iconfilename)!=''">
									<fo:external-graphic scaling="uniform" content-height="scale-to-fit" max-height="6mm" margin-top="5mm">
									<xsl:attribute name="src"><xsl:value-of select="php:function('xsl_transform_imagepath', $iconfilename)"></xsl:value-of></xsl:attribute>
									</fo:external-graphic>
								</xsl:if>

								<!-- content-width="24mm" -->
							</xsl:if>
						</xsl:otherwise> <!-- todo: error msg? -->
					</xsl:choose>
				</fo:inline>
				<!-- ICON TEXT -->
				<fo:inline font-weight="bold">
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
				</fo:inline>
			</fo:block>
			<fo:block><xsl:apply-templates/></fo:block>
		</fo:block>
  	</xsl:template>

	<xsl:template match="heading">
		<xsl:variable name="level" select="@level"></xsl:variable>
		<xsl:choose>
			<xsl:when test=".=ancestor::article/@title">
			
			</xsl:when>
			<xsl:otherwise>
				<fo:block keep-with-next.within-page="always">
					<xsl:attribute name="id">
					<xsl:value-of select="generate-id()"/>
					<!-- 
						<xsl:value-of select="ancestor::article/@title"></xsl:value-of>
						<xsl:text>#</xsl:text>
						<xsl:value-of select="."></xsl:value-of>
						 -->
					</xsl:attribute>
					<xsl:choose>
						<xsl:when test="$level='1'">
							<xsl:call-template name="font_head"></xsl:call-template>
						</xsl:when>
						<xsl:when test="$level='2'">
							<xsl:call-template name="font_subhead"></xsl:call-template>
						</xsl:when>
						<xsl:when test="$level='3'">
							<xsl:call-template name="font_subsubhead"></xsl:call-template>
						</xsl:when>
						<xsl:when test="$level='4'">
							<xsl:call-template name="font_subsubsubhead"></xsl:call-template>
						</xsl:when>						
						<xsl:otherwise>
							<xsl:call-template name="font_subsubsubsubhead"></xsl:call-template>
						</xsl:otherwise>
					</xsl:choose>
					<!-- <xsl:value-of select="."></xsl:value-of> -->
					<xsl:apply-templates></xsl:apply-templates>
				</fo:block>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>			

	<xsl:template match="link">
		<xsl:apply-templates select="php:function('LoopXml::transform_link', .)"></xsl:apply-templates>
	</xsl:template> 
	<xsl:template match="link" mode="loop_object">
		<xsl:apply-templates select="php:function('LoopXml::transform_link', .)"></xsl:apply-templates>
	</xsl:template> 
	
	<xsl:template match="php_link">
		<xsl:value-of select="."></xsl:value-of>
	</xsl:template>
	
	<xsl:template match="php_link_external">
		<fo:basic-link>
			<xsl:attribute name="external-destination"><xsl:value-of select="@href"></xsl:value-of></xsl:attribute>
			<fo:inline text-decoration="underline"><xsl:value-of select="."></xsl:value-of></fo:inline>
			<xsl:text> </xsl:text>
			<fo:inline ><fo:external-graphic scaling="uniform" content-height="scale-to-fit" content-width="2mm" src="/opt/www/loop.oncampus.de/mediawiki/skins/loop/images/print/www_link.png"></fo:external-graphic></fo:inline>
		</fo:basic-link>
	</xsl:template>	

	<xsl:template match="php_link_internal">
		<fo:basic-link text-decoration="underline">
			<xsl:attribute name="internal-destination"><xsl:value-of select="@href"></xsl:value-of></xsl:attribute>
			<xsl:value-of select="."></xsl:value-of>
		</fo:basic-link>
	</xsl:template>
	
	<xsl:template match="php_link_image">
		<xsl:variable name="align">
			<xsl:choose>
				<xsl:when test="ancestor::extension[@extension_name='loop_figure']">inside</xsl:when>			
				<xsl:when test="@align='left'">start</xsl:when>
				<xsl:when test="@align='right'">end</xsl:when>
				<xsl:otherwise>none</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>	
	
		<fo:float>
			<xsl:attribute name="float" value="$align"></xsl:attribute>				
				<xsl:choose>
				<xsl:when test="$align='start'">
					<xsl:attribute name="axf:float-margin-x">5mm</xsl:attribute>
				</xsl:when>			
				<xsl:when test="$align='end'">
					<xsl:attribute name="axf:float-margin-x">5mm</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					
				</xsl:otherwise>
			</xsl:choose>
			
			<xsl:if test="@imagepath">
				<fo:block font-size="0pt" line-height="0pt" padding-start="0pt" padding-end="0pt" padding-top="0pt" padding-bottom="0pt" padding-left="0pt" padding-right="0pt">
					<fo:external-graphic scaling="uniform" content-height="scale-to-fit"  dominant-baseline="reset-size">
						<!-- <xsl:choose>
							<xsl:when test="$align='start'">
								<xsl:attribute name="padding-right">7mm</xsl:attribute>				
							</xsl:when>
							<xsl:when test="$align='end'">
								<xsl:attribute name="padding-right">7mm</xsl:attribute>				
							</xsl:when>					
							<xsl:otherwise>
								<xsl:attribute name="padding-left">0mm</xsl:attribute>
							</xsl:otherwise>
						</xsl:choose> -->							
						<xsl:attribute name="src" ><xsl:value-of select="@imagepath"></xsl:value-of></xsl:attribute>
						<xsl:attribute name="content-width" ><xsl:value-of select="@imagewidth"></xsl:value-of></xsl:attribute>
					</fo:external-graphic>
				</fo:block>
			</xsl:if>
		</fo:float>		
	</xsl:template>	
	
	<xsl:template match="extension" mode="loop_object">
		<xsl:choose>
			<xsl:when test="@extension_name='loop_title'">
				<xsl:apply-templates></xsl:apply-templates>
			</xsl:when>
			<xsl:when test="@extension_name='loop_description'">
				<xsl:apply-templates></xsl:apply-templates>
			</xsl:when>
			<xsl:when test="@extension_name='loop_copyright'">
				<xsl:apply-templates></xsl:apply-templates>
			</xsl:when>			
			<xsl:when test="@extension_name='loop_spoiler_text'">
				<xsl:apply-templates></xsl:apply-templates>
			</xsl:when>
		</xsl:choose>	
	</xsl:template>	

	<xsl:template match="extension">
		<!-- <xsl:if test="not(@extension_name='mathimage')"> -->
		<xsl:if test="@extension_name='loop_figure'">
			<fo:inline>
				  <xsl:attribute name="id"><xsl:value-of select="generate-id()"></xsl:value-of></xsl:attribute>
			</fo:inline>			
		</xsl:if>	
		<xsl:if test="@extension_name='loop_formula'">
			<fo:inline>
				  <xsl:attribute name="id"><xsl:value-of select="generate-id()"></xsl:value-of></xsl:attribute>
			</fo:inline>			
		</xsl:if>	
		<xsl:if test="@extension_name='loop_listing'">
			<fo:inline>
				  <xsl:attribute name="id"><xsl:value-of select="generate-id()"></xsl:value-of></xsl:attribute>
			</fo:inline>			
		</xsl:if>	
		<xsl:if test="@extension_name='loop_media'">
			<fo:inline>
				  <xsl:attribute name="id"><xsl:value-of select="generate-id()"></xsl:value-of></xsl:attribute>
			</fo:inline>			
		</xsl:if>	
		<xsl:if test="@extension_name='loop_table'">
			<fo:inline>
				  <xsl:attribute name="id"><xsl:value-of select="generate-id()"></xsl:value-of></xsl:attribute>
			</fo:inline>			
		</xsl:if>	
		<xsl:if test="@extension_name='loop_task'">
			<fo:inline>
				  <xsl:attribute name="id"><xsl:value-of select="generate-id()"></xsl:value-of></xsl:attribute>
			</fo:inline>			
		</xsl:if>	
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
			<xsl:when test="@extension_name='math'">
				<fo:instream-foreign-object>
					<xsl:copy-of select="php:function('xsl_transform_math', .)"></xsl:copy-of>  
				</fo:instream-foreign-object>
			</xsl:when>
			<xsl:otherwise>
			</xsl:otherwise>
		</xsl:choose>	
	</xsl:template>
	
		<xsl:template name="glossary_exists">
		<xsl:choose>
			<xsl:when test="//*/glossary/article">
				<xsl:text>1</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>0</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>		
	
	<xsl:template name="cite_exists">
		<xsl:choose>
			<xsl:when test="//*/xhtml:cite">
				<xsl:text>1</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>0</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>	
	
	<xsl:template name="figure_exists">
		<xsl:choose>
			<xsl:when test="//*/extension[@extension_name='loop_figure']">
				<xsl:text>1</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>0</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>			
	
	<xsl:template name="table_exists">
		<xsl:choose>
			<xsl:when test="//*/extension[@extension_name='loop_table']">
				<xsl:text>1</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>0</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="media_exists">
		<xsl:choose>
			<xsl:when test="//*/extension[@extension_name='loop_media']">
				<xsl:text>1</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>0</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>	

	<xsl:template name="formula_exists">
		<xsl:choose>
			<xsl:when test="//*/extension[@extension_name='loop_formula']">
				<xsl:text>1</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>0</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>		

	<xsl:template name="listing_exists">
		<xsl:choose>
			<xsl:when test="//*/extension[@extension_name='loop_listing']">
				<xsl:text>1</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>0</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>		
	
	<xsl:template name="task_exists">
		<xsl:choose>
			<xsl:when test="//*/extension[@extension_name='loop_task']">
				<xsl:text>1</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>0</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>		

	<xsl:template name="index_exists">
		<xsl:choose>
			<xsl:when test="(//*/paragraph[starts-with(.,'#index')]) or (//*/extension[@extension_name='loop_index'])">
				<xsl:text>1</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text>0</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>		
	
		<xsl:template name="appendix_number">
		<xsl:param name="content"></xsl:param>
		
		<xsl:variable name="c_bibliography" ><xsl:call-template name="cite_exists"></xsl:call-template></xsl:variable>	
		<xsl:variable name="c_figures" ><xsl:call-template name="figure_exists"></xsl:call-template></xsl:variable>
		<xsl:variable name="c_tables" ><xsl:call-template name="table_exists"></xsl:call-template></xsl:variable>
		<xsl:variable name="c_media" ><xsl:call-template name="media_exists"></xsl:call-template></xsl:variable>
		<xsl:variable name="c_formulas" ><xsl:call-template name="formula_exists"></xsl:call-template></xsl:variable>
		<xsl:variable name="c_listings" ><xsl:call-template name="listing_exists"></xsl:call-template></xsl:variable>
		<xsl:variable name="c_tasks" ><xsl:call-template name="task_exists"></xsl:call-template></xsl:variable>
		<xsl:variable name="c_index" ><xsl:call-template name="index_exists"></xsl:call-template></xsl:variable>
		<xsl:variable name="c_glossary" ><xsl:call-template name="glossary_exists"></xsl:call-template></xsl:variable>

		<xsl:variable name="temp_nr">
			<xsl:choose>
				<xsl:when test="$content='bibliography'">
					<xsl:value-of select="$c_bibliography"></xsl:value-of>
				</xsl:when>
				<xsl:when test="$content='list_of_figures'">
					<xsl:value-of select="$c_bibliography + $c_figures"></xsl:value-of>
				</xsl:when>				
				<xsl:when test="$content='list_of_tables'">
					<xsl:value-of select="$c_bibliography + $c_figures + $c_tables"></xsl:value-of>
				</xsl:when>
				<xsl:when test="$content='list_of_media'">
					<xsl:value-of select="$c_bibliography + $c_figures + $c_tables + $c_media"></xsl:value-of>
				</xsl:when>				
				<xsl:when test="$content='list_of_formulas'">
					<xsl:value-of select="$c_bibliography + $c_figures + $c_tables + $c_media + $c_formulas"></xsl:value-of>
				</xsl:when>		
				<xsl:when test="$content='list_of_listings'">
					<xsl:value-of select="$c_bibliography + $c_figures + $c_tables + $c_media + $c_formulas + $c_listings"></xsl:value-of>
				</xsl:when>
				<xsl:when test="$content='list_of_tasks'">
					<xsl:value-of select="$c_bibliography + $c_figures + $c_tables + $c_media + $c_formulas + $c_listings + $c_tasks"></xsl:value-of>
				</xsl:when>
				<xsl:when test="$content='index'">
					<xsl:value-of select="$c_bibliography + $c_figures + $c_tables + $c_media + $c_formulas + $c_listings + $c_tasks + $c_index"></xsl:value-of>
				</xsl:when>												
				<xsl:when test="$content='glossary'">
					<xsl:value-of select="$c_bibliography + $c_figures + $c_tables + $c_media + $c_formulas + $c_listings + $c_tasks + $c_index + $c_glossary"></xsl:value-of>
				</xsl:when>																												
			</xsl:choose>
		</xsl:variable>
		<xsl:choose>
			<xsl:when test="$temp_nr='1'"><xsl:text>I</xsl:text></xsl:when>
			<xsl:when test="$temp_nr='2'"><xsl:text>II</xsl:text></xsl:when>
			<xsl:when test="$temp_nr='3'"><xsl:text>III</xsl:text></xsl:when>
			<xsl:when test="$temp_nr='4'"><xsl:text>IV</xsl:text></xsl:when>
			<xsl:when test="$temp_nr='5'"><xsl:text>V</xsl:text></xsl:when>
			<xsl:when test="$temp_nr='6'"><xsl:text>VI</xsl:text></xsl:when>
			<xsl:when test="$temp_nr='7'"><xsl:text>VII</xsl:text></xsl:when>
			<xsl:when test="$temp_nr='8'"><xsl:text>VIII</xsl:text></xsl:when>
			<xsl:when test="$temp_nr='9'"><xsl:text>IX</xsl:text></xsl:when>
			<xsl:when test="$temp_nr='10'"><xsl:text>X</xsl:text></xsl:when>
		</xsl:choose>

	</xsl:template>
	
	<xsl:template match="extension" mode="infigure">
		<xsl:choose>
			<xsl:when test="@extension_name='loop_figure_title'">
				<xsl:apply-templates select="node()[not(self::br) and not(self::xhtml:br)]"></xsl:apply-templates>
			</xsl:when>
			<xsl:when test="@extension_name='loop_figure_description'">
				<xsl:apply-templates select="node()[not(self::br) and not(self::xhtml:br)]"></xsl:apply-templates>
			</xsl:when>	
			<xsl:when test="@extension_name='loop_title'">
				<!-- <xsl:apply-templates  mode="infigure"></xsl:apply-templates> -->
				<xsl:apply-templates select="node()[not(self::br) and not(self::xhtml:br)]"></xsl:apply-templates>
			</xsl:when>	
			<xsl:when test="@extension_name='loop_description'">
				<xsl:apply-templates select="node()[not(self::br) and not(self::xhtml:br)]"></xsl:apply-templates>
			</xsl:when>	
			<xsl:when test="@extension_name='loop_copyright'">
				<xsl:apply-templates select="node()[not(self::br) and not(self::xhtml:br)]"></xsl:apply-templates>
			</xsl:when>		
				
		</xsl:choose>
	</xsl:template>	
	
</xsl:stylesheet>