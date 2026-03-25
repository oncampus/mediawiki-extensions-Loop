<xsl:stylesheet version="1.0"
				xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format"
				xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2004/07/xpath-functions"
				xmlns:xdt="http://www.w3.org/2004/07/xpath-datatypes" xmlns:fox="http://xml.apache.org/fop/extensions"
				xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:exsl="http://exslt.org/common"
				xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:func="http://exslt.org/functions"
				xmlns:php="http://php.net/xsl" xmlns:str="http://exslt.org/strings"
				xmlns:axf="http://www.antennahouse.com/names/XSL/Extensions"
				extension-element-prefixes="func php str" xmlns:functx="http://www.functx.com" exclude-result-prefixes="xhtml">

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

</xsl:stylesheet>
