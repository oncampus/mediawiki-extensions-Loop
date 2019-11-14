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
		<xsl:param name="terminology_exists"><xsl:call-template name="terminology_exists"></xsl:call-template></xsl:param>	
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
		
			<xsl:if test="($cite_exists='1') or ($figure_exists='1') or ($table_exists='1') or ($media_exists='1') or ($formula_exists='1') or ($listing_exists='1') or ($task_exists='1') or ($index_exists='1') or ($glossary_exists='1') or ($terminology_exists='1')">
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
		<xsl:param name="terminology_exists"><xsl:call-template name="terminology_exists"></xsl:call-template></xsl:param>
		<fo:page-sequence master-reference="full-page" id="appendix_sequence">
			<fo:static-content font-family="{$font_family}" flow-name="xsl-region-before">
				<xsl:call-template name="default-header"></xsl:call-template>			
			</fo:static-content>			
			<fo:static-content font-family="{$font_family}" flow-name="xsl-region-after">
				<xsl:call-template name="default-footer"></xsl:call-template>
			</fo:static-content>
			<fo:flow font-family="{$font_family}" flow-name="xsl-region-body">
				<xsl:call-template name="page-content-appendix"></xsl:call-template>
				
				<xsl:if test="$cite_exists='1'">
						<xsl:call-template name="page-content-bibliography"></xsl:call-template>
				</xsl:if>
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

		<xsl:if test="$terminology_exists='1'">
			<fo:page-sequence master-reference="full-page" id="terminology_sequence">
				<fo:static-content font-family="{$font_family}" flow-name="xsl-region-before">
					<xsl:call-template name="default-header"></xsl:call-template>			
				</fo:static-content>			
				<fo:static-content font-family="{$font_family}" flow-name="xsl-region-after">
					<xsl:call-template name="default-footer"></xsl:call-template>
				</fo:static-content>
				<fo:flow font-family="{$font_family}" flow-name="xsl-region-body">
					<xsl:call-template name="page-content-terminology"></xsl:call-template>
				</fo:flow>
			</fo:page-sequence>	         	
        </xsl:if>	
		
		
	</xsl:template>			
	
	<xsl:template name="page-content-index">
		<fo:block>
			<fo:marker marker-class-name="page-title-left">
				<xsl:value-of select="$word_appendix"></xsl:value-of>
			</fo:marker>
		</fo:block>
		<fo:block>
			<fo:marker marker-class-name="page-title-right">
				<xsl:call-template name="appendix_number">
					<xsl:with-param name="content" select="'index'"></xsl:with-param>
				</xsl:call-template>
				<xsl:text> </xsl:text>			
				<xsl:value-of select="$word_index"></xsl:value-of>
			</fo:marker>
		</fo:block>
		<fo:block id="index" keep-with-next="always">
			<xsl:call-template name="font_head"></xsl:call-template>
				<xsl:call-template name="appendix_number">
					<xsl:with-param name="content" select="'index'"></xsl:with-param>
				</xsl:call-template>
				<xsl:text> </xsl:text>			
			<xsl:value-of select="$word_index"></xsl:value-of>
		</fo:block>
		<fo:block>
			<xsl:apply-templates select="php:function('LoopXsl::xsl_getIndex', '')"></xsl:apply-templates>
		</fo:block>
	</xsl:template>		
	
	
	<xsl:template match="loop_index_list">
		<xsl:apply-templates></xsl:apply-templates>
	</xsl:template>

	<xsl:template match="loop_index_group">
		<fo:block>
		<fo:block margin-top="5mm" font-weight="bold">
			<xsl:value-of select="@letter"></xsl:value-of>
		</fo:block>
		<xsl:apply-templates></xsl:apply-templates>
		</fo:block>
	</xsl:template>

	<xsl:template match="loop_index_item">
		<fo:block>
			<xsl:apply-templates></xsl:apply-templates>
		</fo:block>
	</xsl:template>

	<xsl:template match="loop_index_title">
		<xsl:value-of select="."></xsl:value-of>
		<xsl:text> </xsl:text>
	</xsl:template>

	<xsl:template match="loop_index_pages">
		<xsl:text> </xsl:text>
		<xsl:apply-templates></xsl:apply-templates>
	</xsl:template>

	<xsl:template match="loop_index_page">
		<xsl:if test="@further=1">
			<xsl:text>,</xsl:text>
		</xsl:if>
		<xsl:text> ... </xsl:text>
		<fo:basic-link >
			<xsl:attribute name="internal-destination"><xsl:value-of select="@pagetitle"></xsl:value-of></xsl:attribute>
			<fo:page-number-citation>
				<xsl:attribute name="ref-id" ><xsl:value-of select="@pagetitle"></xsl:value-of></xsl:attribute>
			</fo:page-number-citation>	
		</fo:basic-link> 
	</xsl:template>


	<xsl:template name="page-content-glossary">
		<xsl:param name="cite_exists"><xsl:call-template name="cite_exists"></xsl:call-template></xsl:param>
		<xsl:param name="figure_exists"><xsl:call-template name="figure_exists"></xsl:call-template></xsl:param>
		<xsl:param name="table_exists"><xsl:call-template name="table_exists"></xsl:call-template></xsl:param>
		<xsl:param name="media_exists"><xsl:call-template name="media_exists"></xsl:call-template></xsl:param>
		<xsl:param name="formula_exists"><xsl:call-template name="formula_exists"></xsl:call-template></xsl:param>
		<xsl:param name="task_exists"><xsl:call-template name="task_exists"></xsl:call-template></xsl:param>			
		
		<xsl:if test="($cite_exists='1') or ($figure_exists='1') or ($table_exists='1') or ($media_exists='1') or ($formula_exists='1') or ($task_exists='1')">
			<fo:block break-before="page"></fo:block>
		</xsl:if>	 
		<fo:block>
			<fo:marker marker-class-name="page-title-left">
				<xsl:value-of select="$word_appendix"></xsl:value-of>
			</fo:marker>
		</fo:block>
		<fo:block>
			<fo:marker marker-class-name="page-title-right">
				<xsl:call-template name="appendix_number">
					<xsl:with-param name="content" select="'glossary'"></xsl:with-param>
				</xsl:call-template>
				<xsl:text> </xsl:text>			
				<xsl:value-of select="$word_glossary"></xsl:value-of>
			</fo:marker>
		</fo:block>
		<fo:block id="glossary" keep-with-next="always" margin-bottom="10mm">
			<xsl:call-template name="font_head"></xsl:call-template>
				<xsl:call-template name="appendix_number">
					<xsl:with-param name="content" select="'glossary'"></xsl:with-param>
				</xsl:call-template>
				<xsl:text> </xsl:text>			
			<xsl:value-of select="$word_glossary"></xsl:value-of>
		</fo:block>
		<xsl:apply-templates select="//*/glossary/article"></xsl:apply-templates>
	</xsl:template>		
	
	<xsl:template name="page-content-terminology">
		<fo:block>
			<fo:marker marker-class-name="page-title-left">
				<xsl:value-of select="$word_appendix"></xsl:value-of>
			</fo:marker>
		</fo:block>
		<fo:block>
			<fo:marker marker-class-name="page-title-right">
				<xsl:call-template name="appendix_number">
					<xsl:with-param name="content" select="'terminology'"></xsl:with-param>
				</xsl:call-template>
				<xsl:text> </xsl:text>			
				<xsl:value-of select="$word_terminology"></xsl:value-of>
			</fo:marker>
		</fo:block>
		<fo:block id="terminology" keep-with-next="always" margin-bottom="10mm">
			<xsl:call-template name="font_head"></xsl:call-template>
			<xsl:call-template name="appendix_number">
				<xsl:with-param name="content" select="'terminology'"></xsl:with-param>
			</xsl:call-template>
			<xsl:text> </xsl:text>			
			<xsl:value-of select="$word_terminology"></xsl:value-of>
		</fo:block>
		<xsl:apply-templates select="//*/terminology/article"></xsl:apply-templates>
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
		<xsl:param name="terminology_exists"><xsl:call-template name="terminology_exists"></xsl:call-template></xsl:param>	
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
		<xsl:if test="$cite_exists='1'">
			<fo:block text-align-last="justify">
				<xsl:call-template name="font_normal"></xsl:call-template>
				<fo:basic-link color="black">
					<xsl:attribute name="internal-destination">bibliography</xsl:attribute>
					<xsl:call-template name="appendix_number">
						<xsl:with-param name="content" select="'bibliography'"></xsl:with-param>
					</xsl:call-template> 				
					<xsl:text> </xsl:text><xsl:value-of select="$word_bibliography"></xsl:value-of>
				</fo:basic-link>
				<fo:inline keep-together.within-line="always">
					<fo:leader leader-pattern="dots"></fo:leader>
					<fo:page-number-citation>
						<xsl:attribute name="ref-id">bibliography</xsl:attribute>
					</fo:page-number-citation>
				</fo:inline>
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
		
		<xsl:if test="$index_exists='1'">
			<fo:block text-align-last="justify">
				<xsl:call-template name="font_normal"></xsl:call-template>
				<fo:basic-link color="black">
					<xsl:attribute name="internal-destination">index</xsl:attribute> 
					<xsl:call-template name="appendix_number">
						<xsl:with-param name="content" select="'index'"></xsl:with-param>
					</xsl:call-template>					
					<xsl:text> </xsl:text><xsl:value-of select="$word_index"></xsl:value-of>
				</fo:basic-link> 
				<fo:inline keep-together.within-line="always">
					<fo:leader leader-pattern="dots"></fo:leader>
					<fo:page-number-citation>
						<xsl:attribute name="ref-id">index</xsl:attribute>
					</fo:page-number-citation>
				</fo:inline> 
			</fo:block>		
		</xsl:if> 

		<xsl:if test="$glossary_exists='1'">
			<fo:block text-align-last="justify">
				<xsl:call-template name="font_normal"></xsl:call-template>
				<fo:basic-link color="black">
					<xsl:attribute name="internal-destination">glossary</xsl:attribute>
					<xsl:call-template name="appendix_number">
						<xsl:with-param name="content" select="'glossary'"></xsl:with-param>
					</xsl:call-template>					
					<xsl:text> </xsl:text><xsl:value-of select="$word_glossary"></xsl:value-of>
				</fo:basic-link>
				<fo:inline keep-together.within-line="always">
					<fo:leader leader-pattern="dots"></fo:leader>
					<fo:page-number-citation>
						<xsl:attribute name="ref-id">glossary</xsl:attribute>
					</fo:page-number-citation>
				</fo:inline>
			</fo:block>		
		</xsl:if>

		<xsl:if test="$terminology_exists='1'">
			<fo:block text-align-last="justify">
				<xsl:call-template name="font_normal"></xsl:call-template>
				<fo:basic-link color="black">
					<xsl:attribute name="internal-destination">terminology</xsl:attribute>
					<xsl:call-template name="appendix_number">
						<xsl:with-param name="content" select="'glossary'"></xsl:with-param>
					</xsl:call-template>					
					<xsl:text> </xsl:text><xsl:value-of select="$word_terminology"></xsl:value-of>
				</fo:basic-link>
				<fo:inline keep-together.within-line="always">
					<fo:leader leader-pattern="dots"></fo:leader>
					<fo:page-number-citation>
						<xsl:attribute name="ref-id">terminology</xsl:attribute>
					</fo:page-number-citation>
				</fo:inline>
			</fo:block>		
		</xsl:if>
		
	</xsl:template>		
	
	<xsl:template match="loop_toc_list">
		<xsl:choose>
			<xsl:when test="position() != last()">
				<fo:block margin-bottom="6pt">
					<xsl:call-template name="font_normal"></xsl:call-template>
					<xsl:attribute name="line-height">11.5pt</xsl:attribute>
					<xsl:apply-templates></xsl:apply-templates>
				</fo:block>
			</xsl:when>
			<xsl:otherwise>
				<fo:block margin-bottom="0pt">
					<xsl:call-template name="font_normal"></xsl:call-template>
					<xsl:attribute name="line-height">11.5pt</xsl:attribute>
					<xsl:apply-templates></xsl:apply-templates>
				</fo:block>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	
	<!-- Bibliography -->
	
	<xsl:template name="bibliography" mode="bibliography">
		<xsl:apply-templates mode="bibliography"/>
	</xsl:template>
	
	<xsl:template name="page-content-bibliography">
		<fo:block>
			<fo:marker marker-class-name="page-title-left">
				<xsl:value-of select="$word_appendix"></xsl:value-of>
			</fo:marker>
		</fo:block>
		<fo:block>
			<fo:marker marker-class-name="page-title-right">
				<xsl:call-template name="appendix_number">
					<xsl:with-param name="content" select="'bibliography'"></xsl:with-param>
				</xsl:call-template>
				<xsl:text> </xsl:text>			
				<xsl:value-of select="$word_bibliography"></xsl:value-of>
			</fo:marker>
		</fo:block>
		<fo:block id="bibliography" keep-with-next="always">
			<xsl:call-template name="font_head"></xsl:call-template>
				<xsl:call-template name="appendix_number">
					<xsl:with-param name="content" select="'bibliography'"></xsl:with-param>
				</xsl:call-template>
				<xsl:text> </xsl:text>			
			<xsl:value-of select="$word_bibliography"></xsl:value-of>
		</fo:block>
		<fo:block>
			<xsl:apply-templates select="php:function('LoopXsl::xsl_get_bibliography', '')" mode="bibliography"></xsl:apply-templates>
		</fo:block>
	</xsl:template>		
	
	<xsl:template name="loop_literature">
		<fo:block>
			<xsl:apply-templates select="php:function('LoopXsl::xsl_get_bibliography', .)" mode="bibliography"></xsl:apply-templates>
		</fo:block>
	</xsl:template>
	
	
	<!-- LOOP_OBJECTS -->
	<xsl:template name="loop_object">
		<xsl:param name="object"></xsl:param>
		<xsl:variable name="objectid" select="@id"></xsl:variable>
			<fo:block>
				<xsl:if test="ancestor::extension[@extension_name='loop_area']">
					<!-- <xsl:attribute name="margin-left">0mm</xsl:attribute> -->
				</xsl:if>
				<fo:table table-layout="fixed" content-width="150mm" border-style="solid" border-width="0pt" border-color="black" border-collapse="collapse" padding-start="0pt" padding-end="0pt" padding-top="4mm" padding-bottom="4mm"  padding-right="0pt">
					<!-- <xsl:attribute name="id"><xsl:text>object</xsl:text><xsl:value-of select="@id"></xsl:value-of></xsl:attribute> -->
					<fo:table-column column-number="1" column-width="0.4mm"/>
					<fo:table-column column-number="2">
						<xsl:choose> 
							<xsl:when test="ancestor::extension[@extension_name='loop_area']">
								<xsl:attribute name="column-width">145mm</xsl:attribute>
							</xsl:when>
							<xsl:when test="ancestor::extension[@extension_name='loop_spoiler']">
								<xsl:attribute name="column-width">145mm</xsl:attribute>
							</xsl:when>
							<xsl:otherwise>
								<!-- <xsl:attribute name="margin-left">0mm</xsl:attribute> -->
							</xsl:otherwise>
						</xsl:choose> 
					</fo:table-column>
					<fo:table-body>		
						<fo:table-row keep-together.within-column="auto">
							<fo:table-cell number-columns-spanned="2">
								<xsl:choose> 
									<xsl:when test="ancestor::extension[@extension_name='loop_area']">
										<xsl:attribute name="width">145mm</xsl:attribute>
									</xsl:when>
									<xsl:when test="ancestor::extension[@extension_name='loop_spoiler']">
										<xsl:attribute name="width">145mm</xsl:attribute>
									</xsl:when>
									<xsl:otherwise>
										<!-- <xsl:attribute name="margin-left">0mm</xsl:attribute> -->
									</xsl:otherwise>
								</xsl:choose> 
								<fo:block text-align="left" margin-bottom="1mm">
									<xsl:choose> 
										<xsl:when test="ancestor::extension[@extension_name='loop_area']">
											<xsl:attribute name="margin-left">13mm</xsl:attribute>
										</xsl:when>
										<xsl:otherwise>
											<!-- <xsl:attribute name="margin-left">0mm</xsl:attribute> -->
										</xsl:otherwise>
									</xsl:choose> 
									<xsl:apply-templates mode="loop_object"/> 
								</fo:block>
							</fo:table-cell>	
						</fo:table-row>
						<xsl:if test="count($object[@render]) = 0 or $object[@render!='none']">
							<fo:table-row keep-together.within-column="auto" >
								<fo:table-cell width="0.4mm" background-color="{$accent_color}">
								</fo:table-cell>
								<fo:table-cell width="150mm" text-align="left" padding-right="2mm">
								<xsl:choose> 
									<xsl:when test="ancestor::extension[@extension_name='loop_area']">
										<xsl:attribute name="padding-left">13mm</xsl:attribute>
									</xsl:when>
									<xsl:otherwise>
										<xsl:attribute name="padding-left">1mm</xsl:attribute>
									</xsl:otherwise>
								</xsl:choose> 
									<xsl:if test="ancestor::extension[@extension_name='spoiler']">
										<xsl:attribute name="padding-left">2mm</xsl:attribute>
									</xsl:if>
									<xsl:call-template name="font_object_title"></xsl:call-template>
									<fo:block text-align="left">
									<xsl:if test="ancestor::extension[@extension_name='loop_area']">
										<xsl:attribute name="margin-left">1mm</xsl:attribute>
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
												<!-- <xsl:attribute name="padding-top">5mm</xsl:attribute> -->
											</xsl:otherwise>
										</xsl:choose>
									</fo:block>
									<xsl:if test="count($object[@render]) = 0 or $object[@render!='title']">	
										<xsl:if test="($object/@description) or ($object/descendant::extension[@extension_name='loop_description'])">
											<fo:block text-align="left">
												<!-- <xsl:if test="ancestor::extension[@extension_name='loop_area']">
													<xsl:attribute name="margin-left">15.5mm</xsl:attribute>
												</xsl:if> -->
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
												<xsl:if test="ancestor::extension[@extension_name='loop_area']">
													<!-- <xsl:attribute name="margin-left">15.5mm</xsl:attribute> -->
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
							<xsl:choose>
								<xsl:when test="@id!=''">
									<fo:basic-link>
										<xsl:attribute name="internal-destination">
											<xsl:text>id</xsl:text><xsl:value-of select="@id"></xsl:value-of>
										</xsl:attribute>
										<fo:block>
										<xsl:if test="php:function('LoopXsl::xsl_transform_imagepath', descendant::link/target)!=''">
											<fo:external-graphic scaling="uniform" content-width="24mm" content-height="scale-to-fit" max-height="20mm">
												<xsl:attribute name="src"><xsl:value-of select="php:function('LoopXsl::xsl_transform_imagepath', descendant::link/target)"></xsl:value-of></xsl:attribute>
											</fo:external-graphic>
										</xsl:if>
										</fo:block>
									</fo:basic-link>
									</xsl:when>
									<xsl:otherwise>
										<xsl:if test="php:function('LoopXsl::xsl_transform_imagepath', descendant::link/target)!=''">
											<fo:external-graphic scaling="uniform" content-width="24mm" content-height="scale-to-fit" max-height="20mm">
												<xsl:attribute name="src"><xsl:value-of select="php:function('LoopXsl::xsl_transform_imagepath', descendant::link/target)"></xsl:value-of></xsl:attribute>
											</fo:external-graphic>
										</xsl:if>
									</xsl:otherwise>
								</xsl:choose>	
							</fo:block>
							</xsl:when>
						<xsl:otherwise>
						</xsl:otherwise>
					</xsl:choose>	
				</fo:table-cell>

				<fo:table-cell width="140mm">
					<fo:block text-align-last="justify" text-align="justify">
						<fo:basic-link color="black">
						
							<xsl:attribute name="internal-destination">
							<xsl:choose>
								<xsl:when test="@id!=''">
									<xsl:text>id</xsl:text><xsl:value-of select="@id"></xsl:value-of>
								</xsl:when>
								<xsl:otherwise>
									cover_sequence
								</xsl:otherwise>
							</xsl:choose>	
							</xsl:attribute>
							
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
									<xsl:attribute name="ref-id">
										<xsl:text>id</xsl:text><xsl:value-of select="@id"></xsl:value-of>
									</xsl:attribute>
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
			<fo:static-content flow-name="xsl-footnote-separator">
				<fo:block>
					<fo:leader leader-length="30%" leader-pattern="rule"/>
				</fo:block>
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
			<xsl:when test="@title">
				<fo:block margin-bottom="12mm">
					<fo:block keep-with-next.within-page="always">
						<xsl:call-template name="font_subhead"></xsl:call-template>
						<xsl:value-of select="@title"></xsl:value-of>
					</fo:block>
					<xsl:apply-templates></xsl:apply-templates>
				</fo:block>
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
		<xsl:param name="terminology_exists"><xsl:call-template name="terminology_exists"></xsl:call-template></xsl:param>
		
		
		<xsl:choose>
			<xsl:when test="($glossary_exists='1')">
				<xsl:text>glossary_sequence</xsl:text>
			</xsl:when>		
			<xsl:when test="($index_exists='1')">
				<xsl:text>index_sequence</xsl:text>
			</xsl:when>
			<xsl:when test="($cite_exists='1') or ($figure_exists='1') or ($table_exists='1') or ($media_exists='1') or ($formula_exists='1') or ($listing_exists='1') or ($task_exists='1') or ($glossary_exists='1') or ($terminology_exists='1')">
				<xsl:text>appendix_sequence</xsl:text>
			</xsl:when>			
			<xsl:otherwise>
				<xsl:text>contentpages_sequence</xsl:text>		
			</xsl:otherwise>
		</xsl:choose>	
	</xsl:template>
	
	
<xsl:template name="font_icon">
		<xsl:attribute name="font-size" >16pt</xsl:attribute>
		<xsl:attribute name="line-height" >12pt</xsl:attribute>
		<xsl:attribute name="vertical-align" >middle</xsl:attribute>
	</xsl:template>	

	<xsl:template name="font_small">
		<xsl:attribute name="font-size">9.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">normal</xsl:attribute>
		<xsl:attribute name="line-height">12.5pt</xsl:attribute>
	</xsl:template>

	<xsl:template name="font_smallbold">
		<xsl:attribute name="font-size">9.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">bold</xsl:attribute>
		<xsl:attribute name="line-height">12.5pt</xsl:attribute>
	</xsl:template>

	<xsl:template name="font_smallitalic">
		<xsl:attribute name="font-size">9.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">normal</xsl:attribute>
		<xsl:attribute name="font-style">italic</xsl:attribute>
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
		<xsl:attribute name="margin-bottom">5pt</xsl:attribute>
	</xsl:template>	
		
	<xsl:template name="font_subsubhead">
		<xsl:attribute name="font-size">12.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">bold</xsl:attribute>
		<xsl:attribute name="line-height">18.5pt</xsl:attribute>
		<xsl:attribute name="margin-top">7pt</xsl:attribute>
		<xsl:attribute name="margin-bottom">5pt</xsl:attribute>
	</xsl:template>

	<xsl:template name="font_subhead">
		<xsl:attribute name="font-size">13.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">bold</xsl:attribute>
		<xsl:attribute name="line-height">15.5pt</xsl:attribute>
		<xsl:attribute name="margin-top">7pt</xsl:attribute>
		<xsl:attribute name="margin-bottom">5pt</xsl:attribute>
	</xsl:template>

	<xsl:template name="font_head">
		<xsl:attribute name="font-size">14.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">bold</xsl:attribute>
		<xsl:attribute name="line-height">16.5pt</xsl:attribute>
		<xsl:attribute name="margin-top">7pt</xsl:attribute>
		<xsl:attribute name="margin-bottom">5pt</xsl:attribute>
	</xsl:template>	
	
	<xsl:template name="font_object_title">
		<xsl:attribute name="font-size">9.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">normal</xsl:attribute>
		<xsl:attribute name="line-height">12.5pt</xsl:attribute>
	</xsl:template>	
	
	<xsl:template match="paragraph">
		<fo:block margin-bottom="5pt">
			<xsl:call-template name="font_normal"></xsl:call-template>
			<xsl:apply-templates></xsl:apply-templates>
		</fo:block>
	</xsl:template>
	
	<xsl:template match="paragraph" mode="bibliography">
		<fo:block margin-bottom="5pt" text-indent="-5mm" line-height="20mm" margin-left="5mm">
			<xsl:call-template name="font_normal"></xsl:call-template>
			<xsl:apply-templates></xsl:apply-templates>
		</fo:block>
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
	<xsl:template match="italics" mode="bibliography">
		<fo:inline font-style="italic">
			<xsl:apply-templates></xsl:apply-templates>
		</fo:inline>
	</xsl:template>	
	
	<!-- loop_zip -->
	<xsl:template match="extension[@extension_name='loop_zip']">
		<fo:block>
			<fo:inline>
				<xsl:call-template name="font_icon"></xsl:call-template>
				<xsl:value-of select="$icon_zip"/>
			</fo:inline>
			<xsl:text> </xsl:text>
			<xsl:value-of select="$phrase_interactive_element"/>
				
			<xsl:call-template name="page-link">
				<xsl:with-param name="destination-id"><xsl:value-of select="ancestor::article/@id"/></xsl:with-param>
			</xsl:call-template>
		</fo:block>				
	</xsl:template>

	<!-- H5P -->
	<xsl:template match="extension[@extension_name='h5p']">
		<fo:block>
			<fo:inline>
				<xsl:call-template name="font_icon"></xsl:call-template>
				<xsl:value-of select="$icon_h5p"/>
			</fo:inline>
			<xsl:text> </xsl:text>
			<xsl:value-of select="$phrase_interactive_element"/>
			<xsl:call-template name="page-link">
				<xsl:with-param name="destination-id"><xsl:value-of select="ancestor::article/@id"/></xsl:with-param>
			</xsl:call-template>
		</fo:block>				
	</xsl:template>

	<!-- LearningApps -->
	<xsl:template match="extension[@extension_name='learningapp']">
		<fo:block>
			<fo:inline>
				<xsl:call-template name="font_icon"></xsl:call-template>
				<xsl:value-of select="$icon_learningapps"/>
			</fo:inline>
			<xsl:text> </xsl:text>
			<xsl:value-of select="$phrase_interactive_element"/>
			<xsl:call-template name="page-link">
				<xsl:with-param name="destination-id"><xsl:value-of select="ancestor::article/@id"/></xsl:with-param>
			</xsl:call-template>
		</fo:block>				
	</xsl:template>
	
	<!-- Quizlet -->
	<xsl:template match="extension[@extension_name='quizlet']">
		<fo:block>
			<fo:inline>
				<xsl:call-template name="font_icon"></xsl:call-template>
				<xsl:value-of select="$icon_quizlet"/>
			</fo:inline>
			<xsl:text> </xsl:text>
			<xsl:value-of select="$phrase_interactive_element"/>
				
			<xsl:call-template name="page-link">
				<xsl:with-param name="destination-id"><xsl:value-of select="ancestor::article/@id"/></xsl:with-param>
			</xsl:call-template>
		</fo:block>				
	</xsl:template>

	<!-- Padlet -->
	<xsl:template match="extension[@extension_name='padlet']">
		<fo:block>
			<fo:inline>
				<xsl:call-template name="font_icon"></xsl:call-template>
				<xsl:value-of select="$icon_padlet"/>
			</fo:inline>
			<xsl:text> </xsl:text>
			<xsl:value-of select="$phrase_interactive_element"/>
				
			<xsl:call-template name="page-link">
				<xsl:with-param name="destination-id"><xsl:value-of select="ancestor::article/@id"/></xsl:with-param>
			</xsl:call-template>
		</fo:block>				
	</xsl:template>

	<!-- Prezi -->
	<xsl:template match="extension[@extension_name='prezi']">
		<fo:block>
			<fo:inline>
				<xsl:call-template name="font_icon"></xsl:call-template>
				<xsl:value-of select="$icon_prezi"/>
			</fo:inline>

			<xsl:text> </xsl:text>
			<xsl:value-of select="$phrase_interactive_element"/>
				
			<xsl:call-template name="page-link">
				<xsl:with-param name="destination-id"><xsl:value-of select="ancestor::article/@id"/></xsl:with-param>
			</xsl:call-template>
		</fo:block>				
	</xsl:template>

	<!-- Slideshare -->
	<xsl:template match="extension[@extension_name='slideshare']">
		<fo:block>
			<fo:inline>
				<xsl:call-template name="font_icon"></xsl:call-template>
				<xsl:value-of select="$icon_slideshare"/>
			</fo:inline>
			<xsl:text> </xsl:text>
			<xsl:value-of select="$phrase_interactive_element"/>
				
			<xsl:call-template name="page-link">
				<xsl:with-param name="destination-id"><xsl:value-of select="ancestor::article/@id"/></xsl:with-param>
			</xsl:call-template>
		</fo:block>				
	</xsl:template>
	
	<!-- Loop Spoiler -->
	<xsl:template name="spoiler">
		<fo:block keep-together.within-page="always">
			<fo:block font-weight="bold">
				<fo:inline wrap-option="no-wrap" axf:border-top-left-radius="1mm" axf:border-top-right-radius="1mm" padding-left="1mm" padding-right="1mm" padding-top="1mm" padding-bottom="1mm" border-style="solid" border-width="0.3mm" border-color="{$accent_color}">

					<xsl:attribute name="background-color"><xsl:value-of select="$accent_color"></xsl:value-of></xsl:attribute>
					<xsl:attribute name="color">#ffffff</xsl:attribute>					
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
				</fo:inline>			
		</fo:block>
		<fo:table keep-together.within-column="always" width="150mm" table-layout="fixed" border-collapse="separate" border-style="solid" border-width="0.3mm" border-color="{$accent_color}">
			<fo:table-body>
				<fo:table-row>
					<fo:table-cell width="140mm">
						<xsl:attribute name="padding-top">2mm</xsl:attribute>
						<xsl:attribute name="padding-left">3mm</xsl:attribute>
						<xsl:attribute name="padding-right">3mm</xsl:attribute>
						<xsl:attribute name="padding-end">3mm</xsl:attribute>	
						<fo:block>
							<xsl:apply-templates></xsl:apply-templates>
						</fo:block>
					</fo:table-cell>
				</fo:table-row>
			</fo:table-body>
		</fo:table>
		</fo:block>
	</xsl:template>
	
	<!-- Loop Sidenote-->
	<xsl:template match="extension[@extension_name='loop_sidenote']">
		<fo:float float="left">
			<fo:block-container absolute-position="absolute" left="-35mm" top="1mm" width="32mm" text-align="left">
				<xsl:choose>
					<xsl:when test="@type='marginalnote'">
						<fo:block text-transform="uppercase" >
							<xsl:call-template name="font_smallitalic"></xsl:call-template>
							<xsl:value-of select="."></xsl:value-of>
						</fo:block>
					</xsl:when>
					<xsl:when test="@type='keyword'">
						<fo:block color="{$accent_color}" font-weight="bold" text-transform="uppercase">
							<xsl:call-template name="font_smallbold"></xsl:call-template>
							<xsl:value-of select="."></xsl:value-of>
						</fo:block>
					</xsl:when>
					<xsl:otherwise>
						<fo:block text-transform="uppercase">
							<xsl:call-template name="font_small"></xsl:call-template>
							<xsl:value-of select="."></xsl:value-of>
						</fo:block>
					</xsl:otherwise>
				</xsl:choose>			
			</fo:block-container>
		</fo:float>
	</xsl:template>
	
	<!-- Loop Paragraph -->
	<xsl:template match="extension[@extension_name='loop_paragraph']">
		<fo:table table-layout="auto" border-collapse="separate" width="150mm" margin="3mm 0 3mm 0">
			<fo:table-body>
				<fo:table-row>
					<fo:table-cell width="10mm">
						<fo:block font-family="{$font_family}" color="{$accent_color}" font-size="6mm" text-align="left" padding-top="1mm">
							<xsl:value-of select="$icon_citation"></xsl:value-of>
						</fo:block>
					</fo:table-cell>
					<fo:table-cell width="140mm">
						<fo:block>
							<xsl:apply-templates></xsl:apply-templates>
						</fo:block>								
					</fo:table-cell>
				</fo:table-row>
				<xsl:if test="@copyright">
					<fo:table-row>
						<fo:table-cell>
							<fo:block></fo:block>
						</fo:table-cell>
						<fo:table-cell>
							<fo:block text-align="right" font-style="italic">
								<xsl:value-of select="@copyright"></xsl:value-of>
							</fo:block>								
						</fo:table-cell>
					</fo:table-row>
				</xsl:if>
			</fo:table-body>
		</fo:table>	
	</xsl:template>

	<!-- Loop Print-->
	<xsl:template match="extension[@extension_name='loop_print']">
		<fo:block >
			<fo:block font-family="{$font_family}" color="{$accent_color}" font-size="6mm" text-align="center" padding-bottom="2mm">
				<xsl:value-of select="$icon_print"></xsl:value-of>
			</fo:block>
			<fo:block  padding="2mm" border-bottom="dashed 0.4mm {$accent_color}" border-top="dashed 0.4mm {$accent_color}">
				<xsl:apply-templates></xsl:apply-templates>
			</fo:block>
		</fo:block>
	</xsl:template>

	<!-- Loop NoPrint-->
	<xsl:template match="extension[@extension_name='loop_noprint']">
		<fo:block></fo:block>				
	</xsl:template>
	
	<!-- Loop Score -->
	<xsl:template match="extension[@extension_name='score']">
		<fo:block>
			<xsl:variable name="score" select="."/>
			<xsl:variable name="lang" select="@lang"/>
			<xsl:variable name="scoreimg" select="php:function('LoopXsl::xsl_score', $score, $lang)"/>

			<fo:external-graphic scaling="uniform" content-width="scale-to-fit">
				<xsl:attribute name="src"><xsl:value-of select="$scoreimg"></xsl:value-of></xsl:attribute>
			</fo:external-graphic>
		</fo:block>
	</xsl:template>

	<!-- Loop Toc -->
	<xsl:template match="extension[@extension_name='loop_toc']">
		<fo:block>
			<xsl:apply-templates select="php:function('LoopXsl::xsl_toc', ancestor::article/@id)"></xsl:apply-templates>
		</fo:block>
	</xsl:template>

	<!-- Extension: Quiz -->
	<xsl:template match="extension[@extension_name='quiz']">
		<fo:block>
			<fo:inline>
				<xsl:call-template name="font_icon"></xsl:call-template>
				<xsl:value-of select="$icon_task"/>
			</fo:inline>
			<xsl:text> </xsl:text>
			<xsl:value-of select="$phrase_quiz"/>

			<xsl:call-template name="page-link">
				<xsl:with-param name="destination-id"><xsl:value-of select="ancestor::article/@id"/></xsl:with-param>
			</xsl:call-template>
		</fo:block>		
	</xsl:template>

	<!-- Loop Area -->
	<xsl:template match="extension[@extension_name='loop_area']" name="looparea">
		<fo:table keep-together.within-page="always" table-layout="auto" margin-left="-12.5mm" border-style="solid" border-width="0pt" border-color="black" border-collapse="collapse"  padding-start="0pt" padding-end="0pt" padding-top="0pt" padding-bottom="0pt"  padding-right="0pt" >
			<fo:table-column column-number="1" column-width="10mm" />
			<fo:table-column column-number="2" column-width="6mm" margin-right="-10mm"/>
			<fo:table-column column-number="3" column-width="146mm"/>
			<fo:table-body>
				<fo:table-row>
					<fo:table-cell width="10mm" text-align="center" color="{$accent_color}" >
						<fo:block>
						<!-- ICON IMG -->
						<fo:block font-size="25pt" padding-bottom="2mm" margin-top="1.6mm" >
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
									
										<xsl:if test="php:function('LoopXsl::xsl_transform_imagepath', $iconfilename)!=''">
											<fo:external-graphic scaling="uniform" content-width="scale-to-fit" max-width="13mm" max-height="13mm">
												<xsl:attribute name="src"><xsl:value-of select="php:function('LoopXsl::xsl_transform_imagepath', $iconfilename)"></xsl:value-of></xsl:attribute>
											</fo:external-graphic>
										</xsl:if>

										<!-- content-width="24mm" -->
									</xsl:if>
								</xsl:otherwise> <!-- todo: error msg? -->
							</xsl:choose>
						</fo:block>

						<fo:block font-weight="bold" font-size="8.5pt" margin-right="0mm" line-height="10pt" white-space-treatment="preserve" linefeed-treatment="preserve">
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
						</fo:block>
						</fo:block>
					</fo:table-cell>
				
				<fo:table-cell border-left="solid 0.8mm {$accent_color}">
					<fo:block ></fo:block>
				</fo:table-cell>
				<fo:table-cell padding-left="9mm">
					<fo:block>
						<xsl:apply-templates/>
					</fo:block>
				</fo:table-cell>
			</fo:table-row>
			
		</fo:table-body>	
	</fo:table>
	<fo:block white-space-collapse="false" white-space-treatment="preserve" font-size="0pt" line-height="18.5pt">.</fo:block>
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
		<xsl:apply-templates select="php:function('LoopXml::transform_link', ., ancestor::article/@id)"></xsl:apply-templates>
	</xsl:template> 
	<xsl:template match="link" mode="loop_object">
		<xsl:apply-templates select="php:function('LoopXml::transform_link', ., ancestor::article/@id)"></xsl:apply-templates>
	</xsl:template> 
	
	<xsl:template match="php_link">
		<xsl:value-of select="."></xsl:value-of>
	</xsl:template>
	
	<xsl:template match="php_link_external">
		<fo:basic-link>
			<xsl:attribute name="external-destination"><xsl:value-of select="@href"></xsl:value-of></xsl:attribute>
			<fo:inline text-decoration="underline"><xsl:value-of select="."></xsl:value-of></fo:inline>
			<xsl:text> </xsl:text><!-- 
			<fo:inline ><fo:external-graphic scaling="uniform" content-height="scale-to-fit" content-width="2mm" src="/opt/www/loop.oncampus.de/mediawiki/skins/loop/images/print/www_link.png"></fo:external-graphic></fo:inline>
 -->		</fo:basic-link>
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
						<xsl:attribute name="max-width">145mm</xsl:attribute>
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
			<xsl:when test="@extension_name='loop_task'">
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
				<xsl:if test="@id">
					<xsl:attribute name="id">
						<xsl:text>id</xsl:text><xsl:value-of select="@id"></xsl:value-of>
					</xsl:attribute>
				</xsl:if>
			</fo:inline>			
		</xsl:if>	
		<xsl:if test="@extension_name='loop_formula'">
			<fo:inline>
				<xsl:if test="@id">
					<xsl:attribute name="id">
						<xsl:text>id</xsl:text><xsl:value-of select="@id"></xsl:value-of>
					</xsl:attribute>
				</xsl:if>
			</fo:inline>			
		</xsl:if>	
		<xsl:if test="@extension_name='loop_listing'">
			<fo:inline>
				<xsl:if test="@id">
					<xsl:attribute name="id">
						<xsl:text>id</xsl:text><xsl:value-of select="@id"></xsl:value-of>
					</xsl:attribute>
				</xsl:if>
			</fo:inline>			
		</xsl:if>	
		<xsl:if test="@extension_name='loop_media'">
			<fo:inline>
				<xsl:if test="@id">
					<xsl:attribute name="id">
						<xsl:text>id</xsl:text><xsl:value-of select="@id"></xsl:value-of>
					</xsl:attribute>
				</xsl:if>
			</fo:inline>			
		</xsl:if>	
		<xsl:if test="@extension_name='loop_table'">
			<fo:inline>
				<xsl:if test="@id">
					<xsl:attribute name="id">
						<xsl:text>id</xsl:text><xsl:value-of select="@id"></xsl:value-of>
					</xsl:attribute>
				</xsl:if>
			</fo:inline>			
		</xsl:if>	
		<xsl:if test="@extension_name='loop_task'">
			<fo:inline>
				<xsl:if test="@id">
					<xsl:attribute name="id">
						<xsl:text>id</xsl:text><xsl:value-of select="@id"></xsl:value-of>
					</xsl:attribute>
				</xsl:if>
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
					<xsl:copy-of select="php:function('LoopXsl::xsl_transform_math', .)"></xsl:copy-of>  
				</fo:instream-foreign-object>
			</xsl:when>
			<xsl:when test="@extension_name='loop_reference'">
				<xsl:call-template name="loop_reference">
                	<xsl:with-param name="object" select="."></xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			
			<xsl:when test="@extension_name='syntaxhighlight'">
				<fo:inline>
					<xsl:apply-templates select="php:function('LoopXsl::xsl_transform_syntaxhighlight', .)" mode="syntaxhighlight"></xsl:apply-templates>
					<!-- <xsl:copy-of select="php:function('LoopXsl::xsl_transform_syntaxhighlight', .)"></xsl:copy-of> -->
				</fo:inline>
			</xsl:when>
			<xsl:when test="@extension_name='loop_spoiler'">
				<xsl:call-template name="spoiler"></xsl:call-template>
			</xsl:when>
			<xsl:when test="@extension_name='spoiler'">
				<xsl:call-template name="spoiler"></xsl:call-template>
			</xsl:when>
			<xsl:when test="@extension_name='loop_literature'">
				<xsl:call-template name="loop_literature"></xsl:call-template>
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

	<xsl:template name="terminology_exists">
		<xsl:choose>
			<xsl:when test="//*/terminology/article">
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
		<xsl:variable name="c_terminology" ><xsl:call-template name="terminology_exists"></xsl:call-template></xsl:variable>

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
				<xsl:when test="$content='terminology'">
					<xsl:value-of select="$c_bibliography + $c_figures + $c_tables + $c_media + $c_formulas + $c_listings + $c_tasks + $c_terminology"></xsl:value-of>
				</xsl:when>
				<xsl:when test="$content='index'">
					<xsl:value-of select="$c_bibliography + $c_figures + $c_tables + $c_media + $c_formulas + $c_listings + $c_tasks + $c_terminology + $c_index"></xsl:value-of>
				</xsl:when>												
				<xsl:when test="$content='glossary'">
					<xsl:value-of select="$c_bibliography + $c_figures + $c_tables + $c_media + $c_formulas + $c_listings + $c_tasks + $c_terminology + $c_index + $c_glossary"></xsl:value-of>
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
			<xsl:when test="@extension_name='loop_task'">
				<xsl:apply-templates select="node()[not(self::br) and not(self::xhtml:br)]"></xsl:apply-templates>
			</xsl:when>		
		</xsl:choose>
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
			
		<fo:basic-link color="black" text-decoration="underline">
			<xsl:if test="@id"> 
				<xsl:attribute name="internal-destination">
					<xsl:text>id</xsl:text><xsl:value-of select="@id"></xsl:value-of>
				</xsl:attribute>
			</xsl:if>
		
			<xsl:choose>
				<xsl:when test="$object=''">
					<xsl:choose>
						<xsl:when test="//*/loop_object[@refid = $objectid]/@object_type='loop_figure'">
							<xsl:value-of select="$word_figure_short"></xsl:value-of>
						</xsl:when>
						<xsl:when test="//*/loop_object[@refid = $objectid]/@object_type='loop_formula'">
							<xsl:value-of select="$word_formula_short"></xsl:value-of>
						</xsl:when>
						<xsl:when test="//*/loop_object[@refid = $objectid]/@object_type='loop_listing'">
							<xsl:value-of select="$word_listing_short"></xsl:value-of>
						</xsl:when>
						<xsl:when test="//*/loop_object[@refid = $objectid]/@object_type='loop_media'">
							<xsl:value-of select="$word_media_short"></xsl:value-of>
						</xsl:when>
						<xsl:when test="//*/loop_object[@refid = $objectid]/@object_type='loop_task'">
							<xsl:value-of select="$word_task_short"></xsl:value-of>
						</xsl:when>
						<xsl:when test="//*/loop_object[@refid = $objectid]/@object_type='loop_table'">
							<xsl:value-of select="$word_table_short"></xsl:value-of>
						</xsl:when>
						<xsl:otherwise>
						</xsl:otherwise>
					</xsl:choose>
					<xsl:text> </xsl:text>
					<xsl:if test="//*/loop_object[@refid = $objectid]/object_number">
						<xsl:value-of select="//*/loop_object[@refid = $objectid]/object_number"></xsl:value-of>
					</xsl:if>

					<xsl:if test="$object/@title='true'">
						<xsl:text> </xsl:text>
						<xsl:if test="//*/loop_object[@refid = $objectid]/object_title">
							<xsl:value-of select="//*/loop_object[@refid = $objectid]/object_title"></xsl:value-of>
						</xsl:if>
					</xsl:if>

				</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates/>
				</xsl:otherwise>
			</xsl:choose>

		</fo:basic-link>

	</xsl:template>

	
	<xsl:template match="xhtml:code">
	
		<fo:block linefeed-treatment="preserve" white-space-collapse="false" white-space-treatment="preserve" background-color="#f8f9fa" font-family="SourceCodePro" font-size="8.5pt" line-height="12pt">
			<xsl:apply-templates select="php:function('LoopXsl::xsl_transform_code', .)" mode="syntaxhighlight"></xsl:apply-templates>
			<!-- <xsl:apply-templates></xsl:apply-templates> -->
		</fo:block>

	</xsl:template>
	
	<xsl:template match="xhtml:cite">
		<xsl:variable name="citetext">
			<xsl:value-of select="."></xsl:value-of>
		</xsl:variable>
		<fo:basic-link >
			<xsl:attribute name="internal-destination">bibliography</xsl:attribute>
			<fo:inline text-decoration="underline" font-style="italic">
				<xsl:choose>
					<xsl:when test="php:function('LoopXsl::xsl_transform_cite', .)=''">
						<xsl:value-of select="translate($citetext,'+',' ')"></xsl:value-of>
					</xsl:when>
					<xsl:otherwise>
						<xsl:attribute name="vertical-align">super</xsl:attribute>
						<xsl:attribute name="font-size">0.8em</xsl:attribute>
						<xsl:value-of select="php:function('LoopXsl::xsl_transform_cite', .)"></xsl:value-of>
					</xsl:otherwise>
				</xsl:choose>
			</fo:inline>
			<xsl:text> </xsl:text>		
			</fo:basic-link>
		<fo:inline font-style="italic">
		
			<xsl:choose>
				<xsl:when test="@pages">
					<xsl:text>, </xsl:text>	
					<xsl:value-of select="$word_cite_pages"></xsl:value-of>
					<xsl:value-of select="@pages"></xsl:value-of>
					<xsl:text> </xsl:text>	
				</xsl:when>
				<xsl:when test="@page">
					<xsl:text>, </xsl:text>	
					<xsl:value-of select="$word_cite_page"></xsl:value-of>
					<xsl:value-of select="@page"></xsl:value-of>
					<xsl:text> </xsl:text>	
				</xsl:when>
			</xsl:choose>
				
		</fo:inline>
	</xsl:template>	
	
	<xsl:template match="pre" mode="syntaxhighlight">
		<xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates>
	</xsl:template>
	<xsl:template match="div" mode="syntaxhighlight">
		<fo:block linefeed-treatment="preserve" white-space-collapse="false" hyphenation-character=" " white-space-treatment="preserve" background-color="#f8f9fa" font-family="SourceCodePro" font-size="8.5pt" line-height="12pt">
			<xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates>
		</fo:block>
	</xsl:template>	
	

	<xsl:template match="span" mode="syntaxhighlight">
			<fo:wrapper width="50mm" wrap-option="wrap">
		<xsl:choose>
			<xsl:when test="@class='lineno'">
				<xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates>
			</xsl:when>
			
<xsl:when test="@class='nbsp'"><fo:inline white-space="pre"><xsl:value-of select="."></xsl:value-of></fo:inline></xsl:when>
<xsl:when test="@class='p'"><fo:inline><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='n'"><fo:inline><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='hll'"><fo:inline background-color="#ffffcc"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>

<xsl:when test="@class='c'"><fo:inline color="#408080"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='err'"><fo:inline border-bottom="1px solid #FF0000"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='k'"><fo:inline color="#008000"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='o'"><fo:inline color="#666666 "><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='cm'"><fo:inline color="#408080" font-style="italic"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='cp'"><fo:inline color="#BC7A00"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='c1'"><fo:inline color="#408080" font-style="italic"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='cs'"><fo:inline color="#408080" font-style="italic"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='gd'"><fo:inline color="#A00000"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='ge'"><fo:inline font-style="italic"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='gr'"><fo:inline color="#FF0000"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='gh'"><fo:inline color="#000080" font-weight="bold"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='gi'"><fo:inline color="#00A000"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='go'"><fo:inline color="#888888"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='gp'"><fo:inline color="#000080" font-weight="bold"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='gs'"><fo:inline font-weight="bold"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='gu'"><fo:inline color="#800080" font-weight="bold"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='gt'"><fo:inline color="#0044DD"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='kc'"><fo:inline color="#008000" font-weight="bold"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='kd'"><fo:inline color="#008000" font-weight="bold"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='kn'"><fo:inline color="#008000" font-weight="bold"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='kp'"><fo:inline color="#008000"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='kr'"><fo:inline color="#008000" font-weight="bold"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='kt'"><fo:inline color="#B00040"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='m'"><fo:inline color="#666666"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='s'"><fo:inline color="#BA2121"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='na'"><fo:inline color="#7D9029"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='nb'"><fo:inline color="#008000"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='nc'"><fo:inline color="#0000FF" font-weight="bold"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='no'"><fo:inline color="#880000"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='nd'"><fo:inline color="#AA22FF"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='ni'"><fo:inline color="#999999" font-weight="bold"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='ne'"><fo:inline color="#D2413A" font-weight="bold"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='nf'"><fo:inline color="#0000FF"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='nl'"><fo:inline color="#A0A000"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='nn'"><fo:inline color="#0000FF" font-weight="bold"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='nt'"><fo:inline color="#008000" font-weight="bold"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='nv'"><fo:inline color="#19177C"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='ow'"><fo:inline color="#AA22FF" font-weight="bold"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='w'"><fo:inline color="#bbbbbb"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='mb'"><fo:inline color="#666666"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='mf'"><fo:inline color="#666666"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='mh'"><fo:inline color="#666666"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='mi'"><fo:inline color="#666666"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='mo'"><fo:inline color="#666666"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='sb'"><fo:inline color="#BA2121"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='sc'"><fo:inline color="#BA2121"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='sd'"><fo:inline color="#BA2121" font-style="italic"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when><!-- ; font-style: italic } /* Literal.String.Doc */ -->
<xsl:when test="@class='s2'"><fo:inline color="#BA2121"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='se'"><fo:inline color="#BB6622" font-weight="bold"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when><!-- ; font-weight: bold } /* Literal.String.Escape */ -->
<xsl:when test="@class='sh'"><fo:inline color="#BA2121"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='si'"><fo:inline color="#BB6688" font-weight="bold"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when><!-- ; font-weight: bold } /* Literal.String.Interpol */ -->
<xsl:when test="@class='sx'"><fo:inline color="#008000"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='sr'"><fo:inline color="#BB6688"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='s1'"><fo:inline color="#BA2121"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='ss'"><fo:inline color="#19177C"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='bp'"><fo:inline color="#008000"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='vc'"><fo:inline color="#19177C"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='vg'"><fo:inline color="#19177C"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='vi'"><fo:inline color="#19177C"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
<xsl:when test="@class='il'"><fo:inline color="#666666"><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>
			
<xsl:when test="@class='x'"><fo:inline ><xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates></fo:inline></xsl:when>			
			
			
			<xsl:otherwise>
			
				<!-- C<xsl:value-of select="@class"></xsl:value-of>D<xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates> -->
				<xsl:apply-templates mode="syntaxhighlight"></xsl:apply-templates>
			</xsl:otherwise>
		</xsl:choose>
		
				</fo:wrapper>
	</xsl:template>
	
	<!-- Sidebar -->
	<xsl:template match="extension[@extension_name='loop_sidebar']">
		<fo:block>
			<xsl:choose>
				<xsl:when test="@print='false'">
				</xsl:when>	
				<xsl:otherwise>
					<xsl:if test="php:function('LoopXsl::xsl_getSidebarPage', @page)!=''">
						<fo:leader leader-pattern="rule" leader-length="100%" rule-style="solid" rule-thickness="0.5pt"/>
						<xsl:if test="@title!=''">
							<fo:inline>
								<xsl:call-template name="font_subsubsubhead"></xsl:call-template>
								<xsl:value-of select="@title"></xsl:value-of>
							</fo:inline>
						</xsl:if>
						<xsl:apply-templates select="php:function('LoopXsl::xsl_getSidebarPage', @page)"></xsl:apply-templates>
						<fo:leader leader-pattern="rule" leader-length="100%" rule-style="solid" rule-thickness="0.5pt"/>
					</xsl:if>
				</xsl:otherwise>
			</xsl:choose>
		</fo:block>				
	</xsl:template>

	<!-- EmbedVideo, via MagicWord -->
	<xsl:template match="extension[@extension_name='embed_video']">
		<fo:block>
			<xsl:choose>
				<xsl:when test="@service='youtube'">
					<xsl:if test="@videoid!=''">
						<fo:inline>
							<xsl:call-template name="font_icon"></xsl:call-template>
							<xsl:value-of select="$icon_youtube"/>
						</fo:inline>
						<xsl:text> </xsl:text>
						<xsl:value-of select="$phrase_youtube_video"/>
						<fo:basic-link><!-- qr? -->
							<xsl:variable name="youtubeurl">
								<xsl:text>https://youtu.be/</xsl:text>
								<xsl:value-of select="@videoid"></xsl:value-of>
							</xsl:variable>	
							<xsl:attribute name="external-destination"><xsl:value-of select="$youtubeurl"></xsl:value-of></xsl:attribute>
							<fo:block text-decoration="underline"><xsl:value-of select="$youtubeurl"></xsl:value-of></fo:block>
							<xsl:text> </xsl:text>
						</fo:basic-link>
					</xsl:if>	
				</xsl:when>	
				<xsl:when test="@service!=''">
					<fo:inline>
						<xsl:call-template name="font_icon"></xsl:call-template>
						<xsl:value-of select="$icon_video"/>
					</fo:inline>
					<xsl:text> </xsl:text>
					<xsl:value-of select="$phrase_video"/>
					<xsl:call-template name="page-link">
						<xsl:with-param name="destination-id"><xsl:value-of select="ancestor::article/@id"/></xsl:with-param>
					</xsl:call-template>
				</xsl:when>	
				<xsl:otherwise>
				</xsl:otherwise>
			</xsl:choose>
		</fo:block>				
	</xsl:template>

	<!-- EmbedVideo, via tag. A direct link to the service can't be provided as there are too many options for the input -->
	<xsl:template match="extension[@extension_name='embedvideo']">
		<fo:block>
			<xsl:choose>
				<xsl:when test="@service='youtube'">
					<fo:inline>
						<xsl:call-template name="font_icon"></xsl:call-template>
						<xsl:value-of select="$icon_youtube"/>
					</fo:inline>
					<xsl:text> </xsl:text>
					<xsl:value-of select="$phrase_youtube_video"/>
					<xsl:call-template name="page-link">
						<xsl:with-param name="destination-id"><xsl:value-of select="ancestor::article/@id"/></xsl:with-param>
					</xsl:call-template>
				</xsl:when>	
				<xsl:when test="@service!=''">
					<fo:inline>
						<xsl:call-template name="font_icon"></xsl:call-template>
						<xsl:value-of select="$icon_video"/>
					</fo:inline>
					<xsl:text> </xsl:text>
					<xsl:value-of select="$phrase_video"/>
					
					<xsl:call-template name="page-link">
						<xsl:with-param name="destination-id"><xsl:value-of select="ancestor::article/@id"/></xsl:with-param>
					</xsl:call-template>
				</xsl:when>	
				<xsl:otherwise>
				</xsl:otherwise>
			</xsl:choose>
		</fo:block>				
	</xsl:template>

	<!-- loop_video -->
	<xsl:template match="extension[@extension_name='loop_video']">
		<fo:block>
			<xsl:if test="@image!=''">
				<xsl:variable name="image">
					<xsl:text>File:</xsl:text>
					<xsl:value-of select="@image"></xsl:value-of>
				</xsl:variable>	
				<xsl:if test="php:function('LoopXsl::xsl_transform_imagepath', $image )!=''">
					<fo:external-graphic scaling="uniform" content-height="scale-to-fit" max-height="70mm" max-width="140mm">
						<xsl:attribute name="src"><xsl:value-of select="php:function('LoopXsl::xsl_transform_imagepath', $image )"></xsl:value-of></xsl:attribute>
					</fo:external-graphic>
				</xsl:if>
			</xsl:if>
		</fo:block>		
		<fo:block>
			<fo:inline>
				<xsl:call-template name="font_icon"></xsl:call-template>
				<xsl:value-of select="$icon_video"/>
			</fo:inline>
			<xsl:text> </xsl:text>
			<xsl:value-of select="$phrase_video"/>

			<xsl:call-template name="page-link">
				<xsl:with-param name="destination-id"><xsl:value-of select="ancestor::article/@id"/></xsl:with-param>
			</xsl:call-template>

		</fo:block>				
	</xsl:template>

	<xsl:template match="extension[@extension_name='loop_video_link']">	
		<xsl:call-template name="page-link">
			<xsl:with-param name="destination-id"><xsl:value-of select="@id"/></xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<!-- loop_audio -->
	<xsl:template match="extension[@extension_name='loop_audio']">
		<fo:block>
			<fo:inline>
				<xsl:call-template name="font_icon"></xsl:call-template>
				<xsl:value-of select="$icon_audio"/>
			</fo:inline>
			<xsl:text> </xsl:text>
			<xsl:value-of select="$phrase_audio"/>
			<xsl:call-template name="page-link">
				<xsl:with-param name="destination-id"><xsl:value-of select="ancestor::article/@id"/></xsl:with-param>
			</xsl:call-template>
		</fo:block>				
	</xsl:template>
	
	<xsl:template name="page-link">
		<xsl:param name="destination-id"></xsl:param>
		<xsl:variable name="pageurl">
			<xsl:value-of select="php:function('LoopXsl::get_page_link', $destination-id)"></xsl:value-of>
		</xsl:variable>	
		
		<xsl:if test="$pageurl">
			<fo:basic-link><!-- qr? -->
				<xsl:attribute name="external-destination"><xsl:value-of select="$pageurl"></xsl:value-of></xsl:attribute>
				<fo:block text-decoration="underline"><xsl:value-of select="$pageurl"></xsl:value-of></fo:block>
				<xsl:text> </xsl:text>
			</fo:basic-link>
			
		</xsl:if>
	</xsl:template>

	
	<!-- loop_audio -->
	<xsl:template match="extension[@extension_name='ref']">

		<fo:footnote>
			<fo:inline baseline-shift="super" font-size="70%">
				<xsl:text>[</xsl:text>
					<xsl:value-of select="count(preceding-sibling::extension[@extension_name='ref'])+1"/>
				<xsl:text>]</xsl:text>
			</fo:inline>
			<fo:footnote-body>
				<fo:list-block provisional-distance-between-starts="1mm" line-height="11.5pt">
					<fo:list-item>
						<fo:list-item-label>
							<fo:block>
								<fo:inline baseline-shift="super" font-size="60%">
									<xsl:text>[</xsl:text>
										<xsl:value-of select="count(preceding-sibling::extension[@extension_name='ref'])+1"/>
									<xsl:text>]</xsl:text>
								</fo:inline>
							</fo:block>
						</fo:list-item-label>
						<fo:list-item-body>
							<fo:block margin-left="3.5mm" font-size="85%">
								<xsl:apply-templates></xsl:apply-templates>
							</fo:block>
						</fo:list-item-body>
					</fo:list-item>
				</fo:list-block>
			</fo:footnote-body>
		</fo:footnote>
	
	</xsl:template>

	<xsl:template match="list">
		<xsl:variable name="listlevel">
			<xsl:value-of select="count(ancestor::list)"></xsl:value-of>
		</xsl:variable>
		<fo:list-block
			start-indent="inherited-property-value(&apos;start-indent&apos;) + 2mm"
			provisional-label-separation="2mm" space-before="4pt" space-after="4pt"
			display-align="before">
			<xsl:choose>
				<xsl:when test="@type='numbered'">
					<xsl:choose>
						<xsl:when test="$listlevel=0">
							<xsl:attribute name="provisional-distance-between-starts"><xsl:value-of select="'6mm'"></xsl:value-of></xsl:attribute>
						</xsl:when>
						<xsl:when test="$listlevel=1">
							<xsl:attribute name="provisional-distance-between-starts"><xsl:value-of select="'8mm'"></xsl:value-of></xsl:attribute>
						</xsl:when>
						<xsl:when test="$listlevel=2">
							<xsl:attribute name="provisional-distance-between-starts"><xsl:value-of select="'10mm'"></xsl:value-of></xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="provisional-distance-between-starts"><xsl:value-of select="'12mm'"></xsl:value-of></xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="provisional-distance-between-starts"><xsl:value-of select="'6mm'"></xsl:value-of></xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:apply-templates></xsl:apply-templates>
		</fo:list-block>
	</xsl:template>

	<xsl:template match="listitem">
		<xsl:variable name="listlevel">
			<xsl:value-of select="count(ancestor::list)"></xsl:value-of>
		</xsl:variable>
		<fo:list-item>
			<fo:list-item-label end-indent="label-end()">
				<xsl:choose>
					<xsl:when test="../@type='numbered'">
						
						<xsl:choose>
								<xsl:when test="$listlevel=1">
									<fo:block><xsl:number level="single" count="listitem" format="1." /></fo:block>
								</xsl:when>
								<xsl:when test="$listlevel=2">
									<fo:block><xsl:number level="multiple" count="listitem" format="1." /></fo:block>
								</xsl:when>
								<xsl:when test="$listlevel=3">
									<fo:block><xsl:number level="multiple" count="listitem" format="1." /></fo:block>
								</xsl:when>
								<xsl:otherwise>
									<fo:block><xsl:number level="multiple" count="listitem" format="1." /></fo:block>
								</xsl:otherwise>
						</xsl:choose>
						
					</xsl:when>
					<xsl:when test="../@type='ident'">
						<fo:block padding-before="2pt"></fo:block>
					</xsl:when>						
					<xsl:otherwise>
						<fo:block padding-before="2pt">
							<xsl:choose>
								<xsl:when test="$listlevel=1">&#x2022;</xsl:when>
								<xsl:when test="$listlevel=2">&#x20D8;</xsl:when>
								<xsl:when test="$listlevel=3">&#x220E;</xsl:when>
								<xsl:otherwise>&#x220E;</xsl:otherwise>
							</xsl:choose>
						</fo:block>
					</xsl:otherwise>
				</xsl:choose>
			</fo:list-item-label>
			<fo:list-item-body start-indent="body-start()">
				<fo:block>
					<xsl:apply-templates select="*[not(name()='list')] | text()"></xsl:apply-templates>
				</fo:block>
				<xsl:apply-templates select="list"></xsl:apply-templates>
			</fo:list-item-body>
		</fo:list-item>
	</xsl:template>	

	<xsl:template match="table">
		<fo:table table-layout="auto" border-style="solid" border-width="0.5pt" border-color="black" border-collapse="collapse" padding="0.6pt" space-after="12.5pt">
				<fo:table-body>
					<xsl:apply-templates></xsl:apply-templates>
				</fo:table-body>
		</fo:table>
	</xsl:template>

	<xsl:template match="tablerow">

		<fo:table-row keep-together.within-column="auto">
			<xsl:apply-templates></xsl:apply-templates>
		</fo:table-row>

	</xsl:template>

    <xsl:template match="tablecell">
        <fo:table-cell>
        	<xsl:attribute name="padding">3pt</xsl:attribute>
        	<xsl:attribute name="border-style">solid</xsl:attribute>
        	<xsl:attribute name="border-width">0.5pt</xsl:attribute>
        	<xsl:attribute name="border-color">black</xsl:attribute>
        	<xsl:attribute name="border-collapse">collapse</xsl:attribute>
			
			<xsl:call-template name="css-style-attributes"></xsl:call-template>
			
			
        	<xsl:if test="@colspan">
				<xsl:attribute name="number-columns-spanned"><xsl:value-of select="@colspan"></xsl:value-of></xsl:attribute>
        	</xsl:if>
        	<xsl:if test="@rowspan">
				<xsl:attribute name="number-rows-spanned"><xsl:value-of select="@rowspan"></xsl:value-of></xsl:attribute>
        	</xsl:if>    
		   	                	
        	    	                	
        		<fo:block keep-together.within-column="auto">
        			<xsl:apply-templates></xsl:apply-templates>
			</fo:block>
			
        </fo:table-cell>
    </xsl:template>

    <xsl:template match="tablehead">
        <fo:table-cell>
        	<xsl:attribute name="padding">3pt</xsl:attribute>
        	<xsl:attribute name="border-style">solid</xsl:attribute>
        	<xsl:attribute name="border-width">0.5pt</xsl:attribute>
        	<xsl:attribute name="border-color">black</xsl:attribute>
        	<xsl:attribute name="border-collapse">collapse</xsl:attribute>
			
			<xsl:call-template name="css-style-attributes"></xsl:call-template>
			
        	<xsl:if test="@colspan">
				<xsl:attribute name="number-columns-spanned"><xsl:value-of select="@colspan"></xsl:value-of></xsl:attribute>
        	</xsl:if>
        	<xsl:if test="@rowspan">
				<xsl:attribute name="number-rows-spanned"><xsl:value-of select="@rowspan"></xsl:value-of></xsl:attribute>
        	</xsl:if>        	           	        	
        	
        	<!-- 
			<fo:block font-weight="bold" break-before="column">
        			<xsl:apply-templates></xsl:apply-templates>
			</fo:block>
 			-->
			<fo:block font-weight="bold" >
        			<xsl:apply-templates></xsl:apply-templates>
			</fo:block>		
		
        </fo:table-cell>
    </xsl:template>	
	

</xsl:stylesheet>