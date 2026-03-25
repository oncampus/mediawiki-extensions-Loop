<xsl:stylesheet version="1.0"
				xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format"
				xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2004/07/xpath-functions"
				xmlns:xdt="http://www.w3.org/2004/07/xpath-datatypes" xmlns:fox="http://xml.apache.org/fop/extensions"
				xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:exsl="http://exslt.org/common"
				xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:func="http://exslt.org/functions"
				xmlns:php="http://php.net/xsl" xmlns:str="http://exslt.org/strings"
				xmlns:axf="http://www.antennahouse.com/names/XSL/Extensions"
				extension-element-prefixes="func php str" xmlns:functx="http://www.functx.com" exclude-result-prefixes="xhtml">

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

		<xsl:if test="$terminology_exists='1'">
			<fo:block text-align-last="justify">
				<xsl:call-template name="font_normal"></xsl:call-template>
				<fo:basic-link color="black">
					<xsl:attribute name="internal-destination">terminology</xsl:attribute>
					<xsl:call-template name="appendix_number">
						<xsl:with-param name="content" select="'terminology'"></xsl:with-param>
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


	</xsl:template>

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

				<xsl:if test="php:function('LoopXsl::xsl_showPageNumbering')">
					<xsl:value-of select="@tocnumber"></xsl:value-of>
				</xsl:if>

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

</xsl:stylesheet>
