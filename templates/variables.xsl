<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xul="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
	xmlns:xbl="http://www.mozilla.org/xbl"
	xmlns:php="http://php.net/xsl">
	<xsl:param name="IconsBase" />
	<xsl:param name="theme" />
	<xsl:output indent="no" method="xml" omit-xml-declaration="yes" encoding="UTF-8" />
	
	<xsl:template match="/">
		<bindings xmlns="http://www.mozilla.org/xbl" xmlns:xbl="http://www.mozilla.org/xbl"
			xmlns:html="http://www.w3.org/1999/xhtml"
			xmlns:xul="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul">
			<xsl:apply-templates select="sections" />
		</bindings>
	</xsl:template>
	
	<xsl:template match="sections">
			<binding xmlns="http://www.mozilla.org/xbl" id="cTheme">
				<xsl:attribute name="extends"><xsl:value-of select="php:function('theme_BindingHelper::XSLGetBaseBinding', .)"/></xsl:attribute>
				<content>
					<xul:vbox flex="1">
						<xsl:apply-templates />
					</xul:vbox>
				</content>
				<implementation>
					<field name="mVariables"><xsl:value-of select="php:function('theme_BindingHelper::XSLVariables')"/></field>
					<field name="mPanel">null</field>
					<constructor><![CDATA[
						wCore.debug('constructor cTheme');
						var pNode = this.parentNode;
						while(pNode)
						{
							if ('mVariables' in pNode)
							{
								this.mPanel = pNode;
								this.setInitialValues(this.mPanel.mVariables);
								break;
							}
							pNode = pNode.parentNode;
						}
					]]></constructor>
			
					<destructor><![CDATA[
						wCore.debug('destructor cTheme');
						this.mPanel = null;
					]]></destructor>						
				</implementation>			
			</binding>
	</xsl:template>
		
	<xsl:template match="section">
		<xul:cfieldsgroup >
			<xsl:attribute name="label"><xsl:value-of select="php:function('theme_BindingHelper::XSLGetLabel', .)"/></xsl:attribute>
			<xsl:copy-of select="@class"/>
			<xsl:if test="@image">
				<xsl:attribute name="image"><xsl:value-of select="php:function('theme_BindingHelper::XSLGetImage', .)"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="@hidden">
				<xsl:attribute name="hide-content">true</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates />
		</xul:cfieldsgroup>
	</xsl:template>
		
	<xsl:template match="field">
		<xul:row>
			<xsl:attribute name="anonid">row_<xsl:value-of select="@name" /></xsl:attribute>
			<xsl:value-of select="php:function('theme_BindingHelper::XSLSetDefaultVarInfo', .)"/>
			<xsl:apply-templates select="." mode="fieldLabel"/>
			<xsl:apply-templates select="." mode="fieldInput"/>
		</xul:row>
	</xsl:template>
		
	<xsl:template match="field" mode="fieldLabel" name="fieldLabel" >
		<xul:clabel>
			<xsl:attribute name="id"><xsl:value-of select="@id" />_label</xsl:attribute>
			<xsl:attribute name="control"><xsl:value-of select="@id" /></xsl:attribute>
			<xsl:attribute name="value"><xsl:value-of select="php:function('theme_BindingHelper::XSLGetLabel', .)" /></xsl:attribute>
		</xul:clabel>
	</xsl:template>
	
	<xsl:template match="field" mode="fieldInput" name="fieldInput">
		<xul:cfield>
			<!-- functional attribute -->
			<xsl:copy-of select="@name"/>
			<xsl:copy-of select="@id"/>
			<xsl:attribute name="fieldtype">
				<xsl:value-of select="@type"/>
			</xsl:attribute>
			<xsl:copy-of select="@class"/>	
			<xsl:copy-of select="@required"/>
			<xsl:copy-of select="@listid"/>
			<xsl:copy-of select="@nocache"/>
			<xsl:copy-of select="@emptylabel"/>
			<xsl:if test="@allow">
				<xsl:attribute name="allow">
					<xsl:value-of select="php:function('uixul_DocumentEditorService::XSLExpandAllowAttribute', @allow)"/>
				</xsl:attribute>
			</xsl:if>
			
			<xsl:copy-of select="@allowfile"/>
			<xsl:copy-of select="@mediafoldername"/>
			<xsl:copy-of select="@allowunits"/>			
			<xsl:copy-of select="@moduleselector"/>
			<xsl:copy-of select="@dialog"/>
							
			<!-- common presentation attribute -->
			<xsl:attribute name="defaultvalue">
				<xsl:value-of select="@initialvalue"/>
			</xsl:attribute>			

			<xsl:copy-of select="@disabled"/>
			<xsl:copy-of select="@hidehelp"/>
			<xsl:copy-of select="@shorthelp"/>
			
			<!-- extra presentation attributes -->
			<xsl:copy-of select="@size"/>
			<xsl:copy-of select="@maxlength"/>
			
			<xsl:copy-of select="@cols"/>
			<xsl:copy-of select="@rows"/>
			
			<xsl:copy-of select="@editwidth"/>
			<xsl:copy-of select="@editheight"/>
			<xsl:copy-of select="@blankUrlParams"/>
			
			<xsl:copy-of select="@hidespinbuttons"/>
			<xsl:copy-of select="@increment"/>
			
			<xsl:copy-of select="@hideorder"/>
			<xsl:copy-of select="@hidedelete"/>
			<xsl:copy-of select="@hideselector"/>
			
			<xsl:copy-of select="@hidetime"/>
			<xsl:copy-of select="@timeoffset"/>
			
			<xsl:copy-of select="@orient"/>	
			<xsl:copy-of select="@flex"/>	
			<xsl:copy-of select="@editable"/>
		</xul:cfield>
	</xsl:template>
</xsl:stylesheet>