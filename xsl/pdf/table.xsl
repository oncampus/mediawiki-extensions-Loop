<xsl:stylesheet version="1.0"
				xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format"
				xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2004/07/xpath-functions"
				xmlns:xdt="http://www.w3.org/2004/07/xpath-datatypes" xmlns:fox="http://xml.apache.org/fop/extensions"
				xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:exsl="http://exslt.org/common"
				xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:func="http://exslt.org/functions"
				xmlns:php="http://php.net/xsl" xmlns:str="http://exslt.org/strings"
				xmlns:axf="http://www.antennahouse.com/names/XSL/Extensions"
				extension-element-prefixes="func php str" xmlns:functx="http://www.functx.com" exclude-result-prefixes="xhtml">

	<xsl:template match="table">
		<xsl:variable name="looparea">
			<xsl:choose>
				<xsl:when test="ancestor::extension[@extension_name='loop_area']">true</xsl:when>
				<xsl:otherwise>false</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:variable name="loopspoiler">
			<xsl:choose>
				<xsl:when test="ancestor::extension[@extension_name='loop_spoiler']">true</xsl:when>
				<xsl:when test="ancestor::extension[@extension_name='spoiler']">true</xsl:when>
				<xsl:otherwise>false</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:variable name="loopobject">
			<xsl:choose>
				<xsl:when test="ancestor::extension[@extension_name='loop_figure']">true</xsl:when>
				<xsl:when test="ancestor::extension[@extension_name='loop_table']">true</xsl:when>
				<xsl:when test="ancestor::extension[@extension_name='loop_listing']">true</xsl:when>
				<xsl:when test="ancestor::extension[@extension_name='loop_formula']">true</xsl:when>
				<xsl:when test="ancestor::extension[@extension_name='loop_media']">true</xsl:when>
				<xsl:when test="ancestor::extension[@extension_name='loop_task']">true</xsl:when>
				<xsl:otherwise>false</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:apply-templates select="php:function('LoopXsl::xsl_transform_table_attributes', ., $looparea, $loopspoiler, $loopobject)" mode="xsl_table"></xsl:apply-templates>
	</xsl:template>

	<xsl:template match="table" mode="xsl_table">
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
			<xsl:choose>
				<xsl:when test="ancestor::table[@looparea!=''] and ancestor::table[@loopspoiler!='']">
					<xsl:attribute name="margin-left">0mm</xsl:attribute>
				</xsl:when>
				<xsl:when test="ancestor::table[@looparea!=''] and ancestor::table[@loopobject!='']">
					<xsl:attribute name="margin-left">0mm</xsl:attribute>
				</xsl:when>
				<xsl:when test="ancestor::table[@looparea!='']">
					<xsl:attribute name="margin-left">12.5mm</xsl:attribute>
				</xsl:when>
			</xsl:choose>
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

		<fo:block font-weight="bold" >
			<xsl:choose>
				<xsl:when test="ancestor::table[@looparea!=''] and ancestor::table[@loopspoiler!='']">
					<xsl:attribute name="margin-left">0mm</xsl:attribute>
				</xsl:when>
				<xsl:when test="ancestor::table[@looparea!=''] and ancestor::table[@loopobject!='']">
					<xsl:attribute name="margin-left">0mm</xsl:attribute>
				</xsl:when>
				<xsl:when test="ancestor::table[@looparea!='']">
					<xsl:attribute name="margin-left">12.5mm</xsl:attribute>
				</xsl:when>
			</xsl:choose>
			<xsl:apply-templates></xsl:apply-templates>
		</fo:block>

	</fo:table-cell>
	</xsl:template>

</xsl:stylesheet>
