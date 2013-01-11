<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:xmi="http://schema.omg.org/spec/XMI/2.1"
                xmlns:uml="http://schema.omg.org/spec/UML/2.1.2"
                xmlns:php="http://schemas/phpdoc/4f0fea3190e75"
                xmlns:dyn="http://exslt.org/dynamic"
                >


  <xsl:output method="text" indent="no" />

  <xsl:param name="docUrl">http://nethgui.nethesis.it/dpdev/Documentation/Api/</xsl:param>
  <xsl:param name="compound" />

  <!-- ENTRY POINT -->
  <xsl:template match="/">
    <xsl:variable name="myIndentation" select="'    '" />
    <xsl:variable name="myUrl" select="$docUrl" />

    <xsl:text>digraph Nethgui {&#xA;</xsl:text>
    <xsl:text>    rankdir = "LR"; compound = true; concentrate = true; fontname = "Cantarell"; fontnames="svg"; &#xA;</xsl:text>
    <xsl:text>    node [shape=none,fontname = "Cantarell"] &#xA;</xsl:text>
    <xsl:text>    edge [arrowhead=vee,style=dashed] &#xA;</xsl:text>

    <!-- declare first level elements -->
    <xsl:apply-templates mode="declaration" select="xmi:XMI/uml:Model/*">
      <xsl:with-param name="indentation" select="$myIndentation" />
      <xsl:with-param name="url" select="$myUrl" />
      <xsl:with-param name="namespace" />
    </xsl:apply-templates>
    
    <xsl:text>} /* end of file */&#xA;</xsl:text>
  </xsl:template>

  <!-- package subgraph generation -->
  <xsl:template mode="declaration" match="packagedElement[@xmi:type='uml:Package']" >
    <xsl:param name="indentation" />
    <xsl:param name="url" />
    <xsl:param name="namespace" />

    <xsl:variable name="myIndentation" select="concat($indentation, '    ')" />
    <xsl:variable name="myUrl" select="$url" />
    <xsl:variable name="myNamespace"><xsl:value-of select="$namespace" /><xsl:if test="$namespace != ''">.</xsl:if></xsl:variable>

    <xsl:value-of select="$myIndentation" />subgraph cluster_<xsl:value-of select="@xmi:id" /> {<xsl:text>&#xA;</xsl:text>
    <xsl:value-of select="$myIndentation" /><xsl:text>     &#xA;</xsl:text>
    <xsl:value-of select="$myIndentation" /><xsl:text>    shape=component;&#xA;</xsl:text>
    <xsl:value-of select="$myIndentation" /><xsl:text>    color=grey;&#xA;</xsl:text>
    <xsl:value-of select="$myIndentation" /><xsl:text>    URL=&#x22;</xsl:text><xsl:value-of select="concat($url, 'namespace-', $myNamespace, @name, '.html')" /><xsl:text>&#x22;;&#xA;</xsl:text>
    <xsl:value-of select="$myIndentation" /><xsl:text>    </xsl:text>label = "<xsl:value-of select="@name" />";<xsl:text>&#xA;</xsl:text>
 
    <!-- print out classifier declaration -->
    <xsl:apply-templates mode="declaration" select="packagedElement[@xmi:type='uml:Interface' or @xmi:type='uml:Package']">
        <xsl:with-param name="indentation" select="concat($myIndentation, '    ')" />
        <xsl:with-param name="url" select="$myUrl" />
        <xsl:with-param name="namespace" select="concat($myNamespace, @name)" />
    </xsl:apply-templates>

    <xsl:value-of select="$myIndentation" />} <xsl:text>&#xA;</xsl:text>

    <!-- print out classifier relations 1 -->
    <xsl:value-of select="$myIndentation" /><xsl:text>/* Relations 1 */ &#xA;</xsl:text>
    <xsl:apply-templates mode="relation" select="packagedElement[@xmi:type='uml:Interface']/*">
      <xsl:with-param name="indentation" select="$myIndentation" />
      <xsl:with-param name="url" select="$myUrl" />
    </xsl:apply-templates>


  </xsl:template>

  <!-- generate the package interface index -->
  <xsl:template mode="declaration" match="packagedElement[@xmi:type='uml:Interface']">
    <xsl:param name="indentation" />
    <xsl:param name="url">./</xsl:param>
    <xsl:param name="namespace" />

    <xsl:variable name="myIndentation" select="concat($indentation, '    ')" />
    <xsl:variable name="myNamespace"><xsl:value-of select="$namespace" /><xsl:if test="$namespace != ''">.</xsl:if></xsl:variable>

    <xsl:value-of select="$myIndentation" />n<xsl:value-of select="@xmi:id" /> [label="<xsl:value-of select='@name' />",shape=rect,color=blue,style=rounded,URL=&#x22;<xsl:value-of select="concat($url, 'class-', $myNamespace, @name, '.html')" />&#x22; ];<xsl:text>&#xA;</xsl:text>
  </xsl:template>

  <!-- generate the package class index -->
  <xsl:template mode="declaration" match="packagedElement[@xmi:type='uml:Class']">
    <xsl:param name="indentation" />
    <xsl:param name="url">./</xsl:param>
    <xsl:param name="namespace" />

    <xsl:variable name="myIndentation" select="concat($indentation, '    ')" />
    <xsl:variable name="myNamespace"><xsl:value-of select="$namespace" /><xsl:if test="$namespace != ''">.</xsl:if></xsl:variable>

    <xsl:value-of select="$myIndentation" />n<xsl:value-of select="@xmi:id" /> [label="<xsl:value-of select='@name' />",shape=rect,color=black,URL=&#x22;<xsl:value-of select="concat($url, 'class-', $myNamespace, @name, '.html')" />&#x22;]; <xsl:text>&#xA;</xsl:text>
  </xsl:template>

  <!-- generalization -->
  <xsl:template mode="relation" match="generalization">
    <xsl:param name="indentation" />
      <xsl:value-of select="$indentation" />n<xsl:value-of select="../@xmi:id"/> -&gt; n<xsl:value-of select="@general"/> [style=solid,arrowhead=empty];<xsl:text>&#xA;</xsl:text>      

  </xsl:template>

  <!-- "use" dependency -->
  <xsl:template mode="relation" match="ownedOperation[@visibility='public']">
    <xsl:param name="indentation" />
    <xsl:for-each select="ownedParameter">
      <xsl:for-each select="type">
        <xsl:variable name="refId" select="@xmi:idref"></xsl:variable>
        <xsl:if test="@xmi:idref != ../../../@xmi:id and dyn:evaluate('/descendant::*[@xmi:id=$refId and @xmi:type!=&#x22;uml:DataType&#x22;]')">
          <xsl:value-of select="$indentation" />n<xsl:value-of select="../../../@xmi:id"/> -&gt; n<xsl:value-of select="@xmi:idref"/> 
          <xsl:if test="$compound">
            <xsl:value-of select="concat('[ltail=&#x22;cluster_', ../../../../@xmi:id, '&#x22;]')" />
          </xsl:if>
          <xsl:text>;&#xA;</xsl:text>
        </xsl:if>
      </xsl:for-each>      
    </xsl:for-each>
  </xsl:template>

  <!-- realizations -->
  <xsl:template mode="relation" match="packagedElement[@xmi:type='uml:Realization']">
    <xsl:param name="indentation" />
    <xsl:value-of select="$indentation" />n<xsl:value-of select="@client"/> -&gt; n<xsl:value-of select="@supplier"/> [style=dashed,arrowhead=empty];<xsl:text>&#xA;</xsl:text>      
  </xsl:template> 

  <xsl:template match="*" />
  <xsl:template match="*" mode="relation" />
  <xsl:template match="*" mode="declaration" />

</xsl:stylesheet>
