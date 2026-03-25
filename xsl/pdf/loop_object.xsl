<xsl:stylesheet version="1.0"
				xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format"
				xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2004/07/xpath-functions"
				xmlns:xdt="http://www.w3.org/2004/07/xpath-datatypes" xmlns:fox="http://xml.apache.org/fop/extensions"
				xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:exsl="http://exslt.org/common"
				xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:func="http://exslt.org/functions"
				xmlns:php="http://php.net/xsl" xmlns:str="http://exslt.org/strings"
				xmlns:axf="http://www.antennahouse.com/names/XSL/Extensions"
				extension-element-prefixes="func php str" xmlns:functx="http://www.functx.com" exclude-result-prefixes="xhtml">

	<xsl:template name="loop_object">
		<xsl:param name="object"></xsl:param>
		<xsl:variable name="objectid" select="@id"></xsl:variable>
		<xsl:variable name="rendertype">
			<xsl:choose>
				<xsl:when test="count(@render) = 0">
					<xsl:value-of select="php:function('LoopXsl::xsl_get_rendertype')"></xsl:value-of>
				</xsl:when>
				<xsl:when test="@render = 'default'">
					<xsl:value-of select="php:function('LoopXsl::xsl_get_rendertype')"></xsl:value-of>
				</xsl:when>

				<xsl:when test="@render = 'marked' or @render = 'icon' or @render = 'title' or @render = 'none' ">
					<xsl:value-of select="@render"></xsl:value-of>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="php:function('LoopXsl::xsl_get_rendertype')"></xsl:value-of>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<fo:block>
			<xsl:if test="ancestor::extension[@extension_name='loop_area']">
				<!-- <xsl:attribute name="margin-left">0mm</xsl:attribute> -->
			</xsl:if>
			<fo:table keep-together.within-page="auto" table-layout="fixed" content-width="150mm" border-style="solid" border-width="0pt" border-color="black" border-collapse="collapse" padding-start="0pt" padding-end="0pt" padding-top="4mm" padding-bottom="4mm"  padding-right="0pt">
				<!-- <xsl:attribute name="id"><xsl:text>object</xsl:text><xsl:value-of select="@id"></xsl:value-of></xsl:attribute> -->
				<fo:table-column column-number="1" column-width="0.4mm"/>
				<fo:table-column column-number="2">
					<xsl:choose>
						<xsl:when test="ancestor::extension[@extension_name='loop_area']">
							<xsl:attribute name="column-width">145mm</xsl:attribute>
						</xsl:when>
						<xsl:when test="ancestor::extension[@extension_name='loop_spoiler']">
							<xsl:attribute name="column-width">140mm</xsl:attribute>
						</xsl:when>
						<xsl:when test="ancestor::extension[@extension_name='spoiler']">
							<xsl:attribute name="column-width">140mm</xsl:attribute>
						</xsl:when>
						<xsl:when test="ancestor::table">

						</xsl:when>
						<xsl:otherwise>
							<!-- <xsl:attribute name="margin-left">0mm</xsl:attribute> -->
						</xsl:otherwise>
					</xsl:choose>
				</fo:table-column>
				<fo:table-body>
					<xsl:if test="not($object[@extension_name='loop_task'])">
						<fo:table-row keep-together.within-column="auto">
							<fo:table-cell number-columns-spanned="2">
								<xsl:choose>
									<xsl:when test="ancestor::extension[@extension_name='loop_area']">
										<xsl:attribute name="max-width">145mm</xsl:attribute>
									</xsl:when>
									<xsl:when test="ancestor::extension[@extension_name='spoiler']">
										<xsl:attribute name="max-width">140mm</xsl:attribute>
									</xsl:when>
									<xsl:when test="ancestor::extension[@extension_name='loop_spoiler']">
										<xsl:attribute name="max-width">140mm</xsl:attribute>
									</xsl:when>
									<xsl:otherwise>
										<!-- <xsl:attribute name="margin-left">0mm</xsl:attribute> -->
									</xsl:otherwise>
								</xsl:choose>
								<fo:block text-align="left" margin-bottom="1mm">
									<xsl:choose>
										<xsl:when test="ancestor::extension[@extension_name='loop_area'] and ancestor::extension[@extension_name='loop_spoiler']">
											<xsl:attribute name="margin-left">0mm</xsl:attribute>
										</xsl:when>
										<xsl:when test="ancestor::extension[@extension_name='loop_area'] and ancestor::extension[@extension_name='spoiler']">
											<xsl:attribute name="margin-left">0mm</xsl:attribute>
										</xsl:when>
										<xsl:when test="ancestor::extension[@extension_name='loop_area'] and ancestor::extension[@extension_name='loop_task']">
											<xsl:attribute name="margin-left">0mm</xsl:attribute>
										</xsl:when>
										<xsl:when test="ancestor::extension[@extension_name='loop_area']">
											<xsl:attribute name="margin-left">13.5mm</xsl:attribute>
										</xsl:when>
										<xsl:otherwise>
											<!-- <xsl:attribute name="margin-left">0mm</xsl:attribute> -->
										</xsl:otherwise>
									</xsl:choose>
									<xsl:apply-templates/><!-- mode="loop_object"-->
								</fo:block>
							</fo:table-cell>
						</fo:table-row>
					</xsl:if>

					<xsl:if test="$rendertype != 'none'">
						<fo:table-row keep-together.within-column="auto" >
							<fo:table-cell width="0.4mm" background-color="{$accent_color}">
							</fo:table-cell>
							<fo:table-cell width="150mm" text-align="left" padding-right="2mm">
								<xsl:choose>
									<xsl:when test="ancestor::extension[@extension_name='loop_area']">
										<!-- <xsl:attribute name="padding-left">13mm</xsl:attribute> -->
									</xsl:when>
									<xsl:otherwise>
										<xsl:attribute name="padding-left">1mm</xsl:attribute>
									</xsl:otherwise>
								</xsl:choose>
								<xsl:call-template name="font_object_title"></xsl:call-template>
								<fo:block text-align="left">
									<xsl:choose>
										<xsl:when test="ancestor::extension[@extension_name='loop_area'] and ancestor::extension[@extension_name='loop_spoiler']">
											<xsl:attribute name="margin-left">0mm</xsl:attribute>
										</xsl:when>
										<xsl:when test="ancestor::extension[@extension_name='loop_area'] and ancestor::extension[@extension_name='spoiler']">
											<xsl:attribute name="margin-left">0mm</xsl:attribute>
										</xsl:when>
										<xsl:when test="ancestor::extension[@extension_name='loop_area'] and ancestor::extension[@extension_name='loop_task']">
											<xsl:attribute name="margin-left">0mm</xsl:attribute>
										</xsl:when>
										<xsl:when test="ancestor::extension[@extension_name='loop_area']">
											<xsl:attribute name="margin-left">13.5mm</xsl:attribute>
										</xsl:when>
										<xsl:otherwise>
											<!-- <xsl:attribute name="margin-left">0mm</xsl:attribute> -->
										</xsl:otherwise>
									</xsl:choose>

									<xsl:if test="$rendertype='marked' or $rendertype='icon'">
										<xsl:choose>
											<xsl:when test="$object[@extension_name='loop_figure']">
												<xsl:value-of select="$icon_figure"></xsl:value-of>
												<xsl:text> </xsl:text>
												<xsl:if test="$rendertype='marked'">
													<fo:inline font-weight="bold">
														<xsl:value-of select="$word_figure_short"></xsl:value-of>
													</fo:inline>
												</xsl:if>
											</xsl:when>
											<xsl:when test="$object[@extension_name='loop_formula']">
												<xsl:value-of select="$icon_formula"></xsl:value-of>
												<xsl:text> </xsl:text>
												<xsl:if test="$rendertype='marked'">
													<fo:inline font-weight="bold">
														<xsl:value-of select="$word_formula_short"></xsl:value-of>
													</fo:inline>
												</xsl:if>
											</xsl:when>
											<xsl:when test="$object[@extension_name='loop_listing']">
												<xsl:value-of select="$icon_listing"></xsl:value-of>
												<xsl:text> </xsl:text>
												<xsl:if test="$rendertype='marked'">
													<fo:inline font-weight="bold">
														<xsl:value-of select="$word_listing_short"></xsl:value-of>
													</fo:inline>
												</xsl:if>
											</xsl:when>
											<xsl:when test="$object[@extension_name='loop_media']">
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
												<xsl:if test="$rendertype='marked'">
													<fo:inline font-weight="bold">
														<xsl:value-of select="$word_media_short"></xsl:value-of>
													</fo:inline>
												</xsl:if>
											</xsl:when>
											<xsl:when test="$object[@extension_name='loop_task']">
												<xsl:value-of select="$icon_task"></xsl:value-of>
												<xsl:text> </xsl:text>
												<xsl:if test="$rendertype='marked'">
													<fo:inline font-weight="bold">
														<xsl:value-of select="$word_task_short"></xsl:value-of>
													</fo:inline>
												</xsl:if>
											</xsl:when>
											<xsl:when test="$object[@extension_name='loop_table']">
												<xsl:value-of select="$icon_table"></xsl:value-of>
												<xsl:text> </xsl:text>
												<xsl:if test="$rendertype='marked'">
													<fo:inline font-weight="bold">
														<xsl:value-of select="$word_table_short"></xsl:value-of>
													</fo:inline>
												</xsl:if>
											</xsl:when>
											<xsl:otherwise>
											</xsl:otherwise>
										</xsl:choose>
									</xsl:if>

									<xsl:if test="$rendertype='marked'">
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
								<xsl:if test="$rendertype!='title'">
									<xsl:if test="($object/@description) or ($object/descendant::extension[@extension_name='loop_description'])">
										<fo:block text-align="left">
											<xsl:if test="ancestor::extension[@extension_name='loop_area']">
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

					<xsl:if test="@extension_name='loop_task'">
						<fo:table-row keep-together.within-column="auto">
							<fo:table-cell number-columns-spanned="2">
								<xsl:choose>
									<xsl:when test="ancestor::extension[@extension_name='loop_area']">
										<xsl:attribute name="max-width">145mm</xsl:attribute>
									</xsl:when>
									<xsl:when test="ancestor::extension[@extension_name='spoiler']">
										<xsl:attribute name="max-width">140mm</xsl:attribute>
									</xsl:when>
									<xsl:when test="ancestor::extension[@extension_name='loop_spoiler']">
										<xsl:attribute name="max-width">140mm</xsl:attribute>
									</xsl:when>
									<xsl:otherwise>
										<!-- <xsl:attribute name="margin-left">0mm</xsl:attribute> -->
									</xsl:otherwise>
								</xsl:choose>
								<fo:block text-align="left" margin-bottom="1mm">
									<xsl:choose>
										<xsl:when test="ancestor::extension[@extension_name='loop_area'] and ancestor::extension[@extension_name='loop_spoiler']">
											<xsl:attribute name="margin-left">0mm</xsl:attribute>
										</xsl:when>
										<xsl:when test="ancestor::extension[@extension_name='loop_area'] and ancestor::extension[@extension_name='spoiler']">
											<xsl:attribute name="margin-left">0mm</xsl:attribute>
										</xsl:when>
										<xsl:when test="ancestor::extension[@extension_name='loop_area'] and ancestor::extension[@extension_name='loop_task']">
											<xsl:attribute name="margin-left">0mm</xsl:attribute>
										</xsl:when>
										<xsl:when test="ancestor::extension[@extension_name='loop_area']">
											<xsl:attribute name="margin-left">13.5mm</xsl:attribute>
										</xsl:when>
										<xsl:otherwise>
											<!-- <xsl:attribute name="margin-left">0mm</xsl:attribute> -->
										</xsl:otherwise>
									</xsl:choose>
									<xsl:apply-templates/><!-- mode="loop_object"-->
								</fo:block>
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
							<xsl:variable name="rendertype">
								<xsl:value-of select="php:function('LoopXsl::xsl_get_rendertype')"></xsl:value-of>
							</xsl:variable>
							<xsl:if test="$rendertype = 'marked'">
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
							</xsl:if>
							<fo:inline>
								<xsl:choose>
									<xsl:when test="descendant::extension[@extension_name='loop_title']">
										<xsl:apply-templates select="descendant::extension[@extension_name='loop_title']"  mode="loop_object"></xsl:apply-templates>
									</xsl:when>
									<xsl:when test="descendant::extension[@extension_name='loop_figure_title']">
										<xsl:apply-templates select="descendant::extension[@extension_name='loop_figure_title']" mode="loop_object"></xsl:apply-templates>
									</xsl:when>
									<xsl:when test="@title">
										<xsl:value-of select="@title"></xsl:value-of>
									</xsl:when>
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

</xsl:stylesheet>
