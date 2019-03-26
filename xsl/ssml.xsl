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
		<xsl:element name="speak">
			<xsl:attribute name="voice">
				<xsl:text>1</xsl:text>
			</xsl:attribute>
		<p><xsl:apply-templates/></p>
		</xsl:element>
	</xsl:template>
	
	<xsl:template match="preblock">
		
	</xsl:template>

	
	<xsl:template match="space">
		<xsl:element name="break">
			<xsl:attribute name="time">
				<xsl:text>1200ms</xsl:text>
			</xsl:attribute>
		</xsl:element>
	</xsl:template>		

	<xsl:template match="meta">

	</xsl:template>		
	
</xsl:stylesheet>