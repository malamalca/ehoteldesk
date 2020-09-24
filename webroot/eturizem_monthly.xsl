<?xml version="1.0" encoding="UTF-8"?>
<html xsl:version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<body style="font-family:Arial;font-size:12pt;background-color:#EEEEEE">

<xsl:for-each select="knjigaGostovMesecnoPorocilo/row">
<table width="100%" style="font-size: 10px; border: solid 1px black;">
    <tr>
        <td>Obrat:</td><td><xsl:value-of select="@idNO"/></td>
    </tr><tr>
        <td>Leto:</td><td><xsl:value-of select="@leto"/></td>
    </tr><tr>
        <td>Mesec:</td><td><xsl:value-of select="@mesec"/></td>
    </tr><tr>
        <td>Status:</td><td><xsl:value-of select="@statusPor"/></td>
    </tr><tr>
        <td>Dod. ležišča:</td><td><xsl:value-of select="@stDodatnihlezisc"/></td>
    </tr><tr>
        <td>Prodane NE:</td><td><xsl:value-of select="@stProdanihNE"/></td>
    </tr><tr>
        <td>Št. obratovalnih dni:</td><td><xsl:value-of select="@odprtDni"/></td>
    </tr>
</table>
</xsl:for-each>

</body>
</html>
