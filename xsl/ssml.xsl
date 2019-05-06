<?xml version="1.0" encoding="UTF-8"?>
<!--
	xmlns="http://www.w3.org/2001/10/synthesis" 
	-->
<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
	xsi:schemaLocation="http://www.w3.org/2001/10/synthesis	http://www.w3.org/TR/speech-synthesis11/synthesis.xsd" 
	 xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:func="http://exslt.org/functions" extension-element-prefixes="func" xmlns:functx="http://www.functx.com">
	
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
				
					<xsl:value-of select="$word_chapter"/>
					<xsl:text> </xsl:text>
					<xsl:value-of select="@tocnumber"></xsl:value-of>
					
					<xsl:element name="break">
						<xsl:attribute name="strength">
							<xsl:text>medium</xsl:text>
						</xsl:attribute>
					</xsl:element>

					<xsl:value-of select="@toctext"></xsl:value-of>
					
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

	
		<xsl:if test="@extension_name='loop_title'">
			<xsl:apply-templates/>
		</xsl:if>	
		<xsl:if test="@extension_name='loop_description'">
			<xsl:apply-templates/>
		</xsl:if>	
		<xsl:if test="@extension_name='loop_copyright'">
				<xsl:apply-templates/>
		</xsl:if>	

		<xsl:if test="@extension_name='loop_figure'">
				
				<xsl:variable name="objectid" select="@id"></xsl:variable>
				<xsl:choose>
					<xsl:when test="//*/loop_object[@refid = $objectid]/object_number">
						<xsl:value-of select="$phrase_figure_number"/>
						<xsl:value-of select="//*/loop_object[@refid = $objectid]/object_number"></xsl:value-of>
						<xsl:text>.</xsl:text>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="$phrase_figure"/>
					</xsl:otherwise>
				</xsl:choose>

				<xsl:if test="@title">
					<xsl:value-of select="@title"></xsl:value-of>
					<xsl:element name="break">
						<xsl:attribute name="strength">
							<xsl:text>medium</xsl:text>
						</xsl:attribute>
					</xsl:element>
				</xsl:if>	
				<xsl:if test="@description">
					<xsl:value-of select="@description"></xsl:value-of>
					<xsl:element name="break">
						<xsl:attribute name="strength">
							<xsl:text>medium</xsl:text>
						</xsl:attribute>
					</xsl:element>
				</xsl:if>	
				<xsl:if test="@copyright">
					<xsl:value-of select="@copyright"></xsl:value-of>
					<xsl:element name="break">
						<xsl:attribute name="strength">
							<xsl:text>medium</xsl:text>
						</xsl:attribute>
					</xsl:element>
				</xsl:if>	
				
				<xsl:apply-templates/>

		</xsl:if>	

		<!--<xsl:element name="speak">
			<xsl:attribute name="voice">
				<xsl:text>2</xsl:text>
			</xsl:attribute>
			
			<xsl:apply-templates/>
		</xsl:element>-->
		
		<xsl:element name="break">
			<xsl:attribute name="time">
				<xsl:text>1200ms</xsl:text>
			</xsl:attribute>
		</xsl:element>
		
	</xsl:template>

	<xsl:template match="heading">
	
		<xsl:element name="speak">
			<xsl:attribute name="voice">
				<xsl:text>2</xsl:text>
			</xsl:attribute>
			
			<xsl:apply-templates/>
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
				</xsl:element>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<func:function name="functx:select_voice">
	
		<xsl:choose>
			<xsl:when test="extension[@extension_name='loop_figure']">
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